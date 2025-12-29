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
}
