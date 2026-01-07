<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WelcomeMessageLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'welcome_message_id',
        'resident_id',
        'resident_name',
        'resident_phone',
        'status',
        'error_message',
        'rec_id',
        'response_code',
        'api_response',
        'raw_response',
        'sent_at',
    ];

    protected $casts = [
        'api_response' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * ارتباط با پیام خوش‌آمدگویی
     */
    public function welcomeMessage()
    {
        return $this->belongsTo(WelcomeMessage::class);
    }

    /**
     * دریافت لاگ‌های با وضعیت خاص
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * دریافت لاگ‌های ارسال شده
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * دریافت لاگ‌های ناموفق
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * دریافت لاگ‌های در انتظار
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * دریافت لاگ‌های اخیر
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * بررسی اینکه آیا پیام با موفقیت ارسال شده است
     */
    public function isSent()
    {
        return $this->status === 'sent';
    }

    /**
     * بررسی اینکه آیا پیام ناموفق بوده است
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * بررسی اینکه آیا پیام در انتظار است
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
}
