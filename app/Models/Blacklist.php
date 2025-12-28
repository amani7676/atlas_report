<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    protected $fillable = [
        'title',
        'blacklist_id',
        'description',
        'is_active',
        'api_response',
        'http_status_code',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
