<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Constant extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'enum_values',
        'description',
    ];

    protected $casts = [
        'enum_values' => 'array',
    ];

    /**
     * دریافت مقدار با تبدیل به نوع مناسب
     */
    public function getFormattedValueAttribute()
    {
        switch ($this->type) {
            case 'number':
                return is_numeric($this->value) ? (float)$this->value : $this->value;
            case 'date':
                return $this->value ? \Carbon\Carbon::parse($this->value)->format('Y-m-d') : null;
            case 'enum':
                return $this->value;
            default:
                return $this->value;
        }
    }

    /**
     * بررسی اعتبار مقدار بر اساس نوع
     */
    public function validateValue($value)
    {
        switch ($this->type) {
            case 'number':
                return is_numeric($value);
            case 'date':
                try {
                    \Carbon\Carbon::parse($value);
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            case 'enum':
                return in_array($value, $this->enum_values ?? []);
            default:
                return true;
        }
    }
}







