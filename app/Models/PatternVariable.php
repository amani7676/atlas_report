<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatternVariable extends Model
{
    protected $fillable = [
        'code',
        'title',
        'pattern_code', // تغییر از table_field به pattern_code
        'table_name',
        'variable_type',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function patterns()
    {
        return $this->belongsToMany(Pattern::class, 'pattern_pattern_variables')
            ->withPivot('variable_code', 'table_field', 'sort_order')
            ->withTimestamps();
    }
}
