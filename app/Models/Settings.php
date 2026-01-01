<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'refresh_interval',
        'api_url',
    ];

    protected $casts = [
        'refresh_interval' => 'integer',
    ];

    /**
     * دریافت تنظیمات (تنها یک رکورد در جدول وجود دارد)
     */
    public static function getSettings()
    {
        return static::first() ?? static::create([
            'refresh_interval' => 5,
            'api_url' => 'http://atlas2.test/api/residents',
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
