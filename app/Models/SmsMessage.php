<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsMessage extends Model
{
    protected $fillable = [
        'title',
        'description',
        'link',
        'text',
        'message_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function smsMessageResidents(): HasMany
    {
        return $this->hasMany(SmsMessageResident::class);
    }
}
