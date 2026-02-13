<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'current_card_type',
        'card_status',
        'card_assigned_at',
    ];

    protected $casts = [
        'card_assigned_at' => 'datetime',
        'card_status' => 'string',
        'current_card_type' => 'string',
    ];

    /**
     * Get the resident that owns this card
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    /**
     * Get card type in Persian
     */
    public function getCardTypeLabelAttribute()
    {
        switch ($this->current_card_type) {
            case 'yellow':
                return 'کارت زرد';
            case 'red':
                return 'کارت قرمز';
            default:
                return 'بدون کارت';
        }
    }

    /**
     * Get status in Persian
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->card_status) {
            case 'pending':
                return 'بررسی نشده';
            case 'approved':
                return 'تأیید شده';
            default:
                return 'نامشخص';
        }
    }
}
