<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'refresh_interval',
        'api_url',
        'sms_delay_before_start',
        'sms_delay_between_messages',
        'welcome_pattern_id',
        'welcome_start_datetime',
    ];

    protected $casts = [
        'refresh_interval' => 'integer',
        'sms_delay_before_start' => 'integer',
        'sms_delay_between_messages' => 'integer',
        'welcome_pattern_id' => 'integer',
        'welcome_start_datetime' => 'datetime',
    ];

    /**
     * رابطه با Pattern برای پیام خوش‌آمدگویی
     */
    public function welcomePattern()
    {
        return $this->belongsTo(Pattern::class, 'welcome_pattern_id');
    }

    /**
     * دریافت تنظیمات (تنها یک رکورد در جدول وجود دارد)
     */
    public static function getSettings()
    {
        return static::first() ?? static::create([
            'refresh_interval' => 5,
            'api_url' => 'http://atlas2.test/api/residents',
            'sms_delay_before_start' => 2,
            'sms_delay_between_messages' => 200,
        ]);
    }

    /**
     * به‌روزرسانی تنظیمات
     */
    public static function updateSettings(array $data)
    {
        $settings = static::getSettings();
        $settings->update($data);
        return $settings;
    }
}
