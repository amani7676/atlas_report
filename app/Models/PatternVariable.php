<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatternVariable extends Model
{
    protected $fillable = [
        'code',
        'title',
        'table_field',
        'table_name',
        'variable_type',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
