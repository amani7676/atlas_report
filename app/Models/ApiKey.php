<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key_name',
        'key_value',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * دریافت API Key بر اساس نام
     */
    public static function getKeyValue($keyName)
    {
        $apiKey = self::where('key_name', $keyName)
            ->where('is_active', true)
            ->first();
        
        return $apiKey ? $apiKey->key_value : null;
    }

    /**
     * تنظیم یا به‌روزرسانی API Key
     */
    public static function setKeyValue($keyName, $keyValue, $description = null)
    {
        return self::updateOrCreate(
            ['key_name' => $keyName],
            [
                'key_value' => $keyValue,
                'description' => $description,
                'is_active' => true,
            ]
        );
    }
}
