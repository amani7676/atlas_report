<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentGrant extends Model
{
    protected $fillable = [
        'resident_id',
        'amount',
        'description',
        'grant_date',
        'is_active',
    ];

    protected $casts = [
        'resident_id' => 'integer',
        'amount' => 'decimal:2',
        'grant_date' => 'date',
        'is_active' => 'boolean',
    ];
    
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * رابطه با اقامت‌گر
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }
}
