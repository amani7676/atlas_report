<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\ResidentApiService;

class SmsMessageResident extends Model
{
    protected $fillable = [
        'sms_message_id',
        'resident_id',
        'resident_name',
        'phone',
        'title',
        'description',
        'status',
        'sent_at',
        'error_message',
        'response_code',
        'api_response',
        'raw_response',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'api_response' => 'array',
    ];

    protected static function booted()
    {
        // همگام‌سازی با API در زمان خواندن
        static::retrieved(function ($model) {
            if ($model->resident_id) {
                $apiService = new ResidentApiService();
                $apiService->syncResidentData($model);
            }
        });
    }

    public function smsMessage(): BelongsTo
    {
        return $this->belongsTo(SmsMessage::class);
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
        $data = $this->getFreshResidentData();
        return $data['name'] ?? $this->resident_name;
    }

    /**
     * Accessor برای دریافت شماره تلفن به‌روز
     */
    public function getFreshPhoneAttribute()
    {
        $data = $this->getFreshResidentData();
        return $data['phone'] ?? $this->phone;
    }
}
