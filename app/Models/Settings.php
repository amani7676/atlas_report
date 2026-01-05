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
    ];

    protected $casts = [
        'refresh_interval' => 'integer',
        'sms_delay_before_start' => 'integer',
        'sms_delay_between_messages' => 'integer',
    ];

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
