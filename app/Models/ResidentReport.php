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
        });

        // همگام‌سازی با API در زمان خواندن
        static::retrieved(function ($model) {
            if ($model->resident_id) {
                $apiService = new ResidentApiService();
                $apiService->syncResidentData($model);
            }
        });
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
            return $this->resident->full_name;
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
            return $this->resident->phone;
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
            return $this->resident ? $this->resident->full_name : null;
        }
        
        // اگر relation load نشده باشد، آن را lazy load می‌کنیم
        // اما فقط اگر resident_id موجود باشد
        if ($this->resident_id) {
            $resident = $this->resident()->first();
            return $resident ? $resident->full_name : null;
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
            return $this->resident ? $this->resident->phone : null;
        }
        
        if ($this->resident_id) {
            $resident = $this->resident()->first();
            return $resident ? $resident->phone : null;
        }
        
        return null;
    }
}
