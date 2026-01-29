<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
        'type',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * دریافت مقدار یک تنظیم
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * تنظیم مقدار یک تنظیم
     */
    public static function setValue($key, $value, $description = null, $type = 'text')
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'type' => $type,
            ]
        );
    }

    /**
     * دریافت تمام تنظیمات به صورت آرایه
     */
    public static function getAllSettings()
    {
        return static::all()->pluck('value', 'key')->toArray();
    }

    /**
     * دریافت تنظیمات پیش‌فرض
     */
    public static function getDefaultSettings()
    {
        return [
            'min_violation_count' => [
                'value' => '1',
                'description' => 'حداقل تعداد تکرار تخلف برای نمایش در جدول تخلفات تکراری',
                'type' => 'number',
            ],
            'min_report_count' => [
                'value' => '1',
                'description' => 'حداقل تعداد گزارش اقامت‌گر برای نمایش در جدول گزارش‌های اقامت‌گران',
                'type' => 'number',
            ],
            'max_violation_score' => [
                'value' => '10',
                'description' => 'حداکثر امتیاز تخلف برای اقامت‌گران برتر',
                'type' => 'number',
            ],
            'summary_top_count' => [
                'value' => '15',
                'description' => 'تعداد نفرات برتر برای نمایش در جدول خلاصه آمار',
                'type' => 'number',
            ],
            'show_most_repeated_violation' => [
                'value' => '1',
                'description' => 'نمایش بخش بیشترین تکرار تخلف',
                'type' => 'boolean',
            ],
            'show_most_violations_person' => [
                'value' => '1',
                'description' => 'نمایش بخش بیشترین تخلفات شخص',
                'type' => 'boolean',
            ],
            'show_all_person_violations' => [
                'value' => '1',
                'description' => 'نمایش جدول کامل تخلفات هر شخص',
                'type' => 'boolean',
            ],
        ];
    }

    /**
     * ایجاد تنظیمات پیش‌فرض اگر وجود نداشته باشند
     */
    public static function createDefaultSettings()
    {
        $defaultSettings = static::getDefaultSettings();
        
        foreach ($defaultSettings as $key => $data) {
            static::setValue($key, $data['value'], $data['description'], $data['type']);
        }
    }
}
