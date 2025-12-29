<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SenderNumber extends Model
{
    protected $fillable = [
        'number',
        'title',
        'description',
        'api_key',
        'is_active',
        'is_pattern',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_pattern' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * دریافت شماره‌های فعال برای پیامک‌های الگویی
     */
    public static function getActivePatternNumbers()
    {
        return static::where('is_active', true)
            ->where('is_pattern', true)
            ->orderBy('priority', 'desc')
            ->orderBy('title')
            ->get();
    }

    /**
     * دریافت شماره‌های فعال برای پیامک‌های ساده
     */
    public static function getActiveSimpleNumbers()
    {
        return static::where('is_active', true)
            ->where('is_pattern', false)
            ->orderBy('priority', 'desc')
            ->orderBy('title')
            ->get();
    }

    /**
     * دریافت شماره پیش‌فرض برای پیامک‌های الگویی
     */
    public static function getDefaultPatternNumber()
    {
        return static::where('is_active', true)
            ->where('is_pattern', true)
            ->orderBy('priority', 'desc')
            ->orderBy('title')
            ->first();
    }

    /**
     * دریافت شماره پیش‌فرض برای پیامک‌های ساده
     */
    public static function getDefaultSimpleNumber()
    {
        return static::where('is_active', true)
            ->where('is_pattern', false)
            ->orderBy('priority', 'desc')
            ->orderBy('title')
            ->first();
    }
}
