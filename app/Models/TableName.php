<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableName extends Model
{
    protected $fillable = [
        'name',
        'table_name',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];
}
