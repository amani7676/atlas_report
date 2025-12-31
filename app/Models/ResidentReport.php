<?php

namespace App\Models;

use App\Events\ResidentReportCreated;
use App\Traits\TriggersAutoSms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\ResidentApiService;

class ResidentReport extends Model
{
    use TriggersAutoSms;
    protected $fillable = [
        'report_id',
        'resident_id',
        'unit_id',
        'room_id',
        'bed_id',
        'notes',
        'has_been_sent',
        'is_checked'
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'room_id' => 'integer',
        'bed_id' => 'integer',
        'resident_id' => 'integer',
        'has_been_sent' => 'boolean',
        'is_checked' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($residentReport) {
            \Log::info('ResidentReport created - triggering event', [
                'resident_report_id' => $residentReport->id,
                'report_id' => $residentReport->report_id,
                'resident_id' => $residentReport->resident_id,
            ]);
            event(new ResidentReportCreated($residentReport));
            
            // بررسی و غیرفعال کردن بخشودگی‌ها بعد از ثبت تخلف جدید
            if ($residentReport->resident_id) {
                $resident = $residentReport->resident;
                if ($resident) {
                    self::checkAndDeactivateGrantsForResident($resident->resident_id);
                }
            }
        });

        // همگام‌سازی با API در زمان خواندن
        static::retrieved(function ($model) {
            if ($model->resident_id) {
                $apiService = new ResidentApiService();
                $apiService->syncResidentData($model);
            }
        });
    }
    
    /**
     * بررسی و غیرفعال کردن بخشودگی‌ها و false کردن is_checked
     */
    public static function checkAndDeactivateGrantsForResident($residentId)
    {
        // دریافت تمام بخشودگی‌های فعال برای این اقامت‌گر
        $activeGrants = \App\Models\ResidentGrant::where('resident_id', $residentId)
            ->where('is_active', true)
            ->orderBy('grant_date', 'desc')
            ->get();
        
        $resident = \App\Models\Resident::where('resident_id', $residentId)->first();
        if (!$resident) {
            return;
        }
        
        foreach ($activeGrants as $grant) {
            // تبدیل تاریخ بخشودگی به Carbon instance برای مقایسه دقیق
            $grantDate = \Carbon\Carbon::parse($grant->grant_date)->startOfDay();
            
            // محاسبه مجموع نمرات منفی تخلف‌های چک نشده که بعد از تاریخ بخشودگی ثبت شده‌اند
            // فقط تخلف‌هایی که تاریخ ثبت آنها >= تاریخ بخشودگی است
            $uncheckedTotalScore = self::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('reports.category_id', 1) // دسته‌بندی تخلف
                ->where('resident_reports.resident_id', $resident->id)
                ->where('resident_reports.is_checked', false)
                ->whereDate('resident_reports.created_at', '>=', $grantDate->toDateString())
                ->sum('reports.negative_score') ?? 0;
            
            // اگر مجموع نمرات منفی >= مقدار بخشودگی
            if ($uncheckedTotalScore >= $grant->amount) {
                // غیرفعال کردن بخشودگی
                $grant->update(['is_active' => false]);
                
                \Log::info('Grant deactivated', [
                    'grant_id' => $grant->id,
                    'resident_id' => $residentId,
                    'grant_amount' => $grant->amount,
                    'unchecked_total_score' => $uncheckedTotalScore,
                    'grant_date' => $grant->grant_date,
                ]);
                
                // false کردن تمام تخلف‌های چک شده این اقامت‌گر
                $updatedCount = self::whereHas('report', function($q) {
                    $q->where('category_id', 1);
                })
                ->where('resident_id', $resident->id)
                ->where('is_checked', true)
                ->update(['is_checked' => false]);
                
                \Log::info('Checked violations unmarked', [
                    'resident_id' => $residentId,
                    'updated_count' => $updatedCount,
                ]);
            }
        }
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
    
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'id');
    }

    public function category()
    {
        return $this->report->category();
    }

    /**
     * دریافت اطلاعات به‌روز اقامت‌گر از API
     */
    public function getFreshResidentData()
    {
        if (!$this->resident_id) {
            return null;
        }

        $apiService = new ResidentApiService();
        return $apiService->getResidentDataForVariables($this->resident_id);
    }

    /**
     * Accessor برای دریافت نام به‌روز اقامت‌گر
     */
    public function getFreshResidentNameAttribute()
    {
        if ($this->resident) {
            return $this->resident->resident_full_name;
        }
        $data = $this->getFreshResidentData();
        return $data['name'] ?? 'نامشخص';
    }

    /**
     * Accessor برای دریافت شماره تلفن به‌روز
     */
    public function getFreshPhoneAttribute()
    {
        if ($this->resident) {
            return $this->resident->resident_phone;
        }
        $data = $this->getFreshResidentData();
        return $data['phone'] ?? null;
    }
    
    /**
     * Accessor برای دریافت نام اقامت‌گر از رابطه
     */
    public function getResidentNameAttribute()
    {
        // استفاده از relation (اگر load شده باشد)
        if ($this->relationLoaded('resident')) {
            if ($this->resident) {
                return $this->resident->resident_full_name ?? null;
            }
            return null;
        }
        
        // اگر relation load نشده باشد، آن را lazy load می‌کنیم
        // اما فقط اگر resident_id موجود باشد
        if ($this->resident_id) {
            $resident = $this->resident()->first();
            return $resident ? ($resident->resident_full_name ?? null) : null;
        }
        
        return null;
    }
    
    /**
     * Accessor برای دریافت نام واحد از رابطه
     */
    public function getUnitNameAttribute()
    {
        if ($this->relationLoaded('resident')) {
            return $this->resident ? $this->resident->unit_name : null;
        }
        
        if ($this->resident_id) {
            $resident = $this->resident()->first();
            return $resident ? $resident->unit_name : null;
        }
        
        return null;
    }
    
    /**
     * Accessor برای دریافت نام اتاق از رابطه
     */
    public function getRoomNameAttribute()
    {
        if ($this->relationLoaded('resident')) {
            return $this->resident ? $this->resident->room_name : null;
        }
        
        if ($this->resident_id) {
            $resident = $this->resident()->first();
            return $resident ? $resident->room_name : null;
        }
        
        return null;
    }
    
    /**
     * Accessor برای دریافت نام تخت از رابطه
     */
    public function getBedNameAttribute()
    {
        if ($this->relationLoaded('resident')) {
            return $this->resident ? $this->resident->bed_name : null;
        }
        
        if ($this->resident_id) {
            $resident = $this->resident()->first();
            return $resident ? $resident->bed_name : null;
        }
        
        return null;
    }
    
    /**
     * Accessor برای دریافت شماره تلفن از رابطه
     */
    public function getPhoneAttribute()
    {
        if ($this->relationLoaded('resident')) {
            return $this->resident ? $this->resident->resident_phone : null;
        }
        
        if ($this->resident_id) {
            $resident = $this->resident()->first();
            return $resident ? $resident->resident_phone : null;
        }
        
        return null;
    }
}
