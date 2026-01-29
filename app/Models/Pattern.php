<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pattern extends Model
{
    protected $fillable = [
        'title',
        'text',
        'pattern_code',
        'blacklist_id',
        'status',
        'rejection_reason',
        'is_active',
        'api_response',
        'http_status_code',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function blacklist()
    {
        // Assuming blacklist_id in patterns table refers to the blacklist_id from Melipayamak, not the local ID
        // If it refers to local ID, change to $this->belongsTo(Blacklist::class);
        return $this->belongsTo(Blacklist::class, 'blacklist_id', 'blacklist_id');
    }
    
    public function reports()
    {
        return $this->belongsToMany(Report::class, 'report_pattern')
            ->withPivot('sort_order', 'is_active')
            ->withTimestamps();
    }
    
    public function patternVariables()
    {
        return $this->belongsToMany(PatternVariable::class, 'pattern_pattern_variables')
            ->withPivot('variable_code', 'sort_order')
            ->orderBy('pivot_sort_order');
    }
    
    public function getPatternVariablesWithCodes()
    {
        return $this->patternVariables()
            ->withPivot('variable_code', 'table_field')
            ->get()
            ->mapWithKeys(function ($variable) {
                // اضافه کردن فیلد table_field از جدول pivot به متغیر
                $variable->pivot_table_field = $variable->pivot->table_field;
                return [$variable->pivot->variable_code => $variable];
            });
    }
    
    public function replaceVariables($text, $data = [])
    {
        $variables = $this->getPatternVariablesWithCodes();
        
        foreach ($variables as $code => $variable) {
            $value = $this->getVariableValue($variable, $data);
            $text = str_replace($code, $value, $text);
        }
        
        return $text;
    }
    
    private function getVariableValue($variable, $data = [])
    {
        $tableField = $variable->table_field;
        
        // اگر داده مستقیم ارسال شده باشد
        if (isset($data[$tableField])) {
            return $data[$tableField];
        }
        
        // اگر داده تو در تو باشد (مثل category.name)
        if (strpos($tableField, '.') !== false) {
            $parts = explode('.', $tableField);
            $value = $data;
            foreach ($parts as $part) {
                if (isset($value[$part])) {
                    $value = $value[$part];
                } else {
                    return '[' . $tableField . ']';
                }
            }
            return $value;
        }
        
        return '[' . $tableField . ']';
    }
}
