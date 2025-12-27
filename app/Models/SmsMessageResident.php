<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsMessageResident extends Model
{
    protected $fillable = [
        'sms_message_id',
        'resident_id',
        'resident_name',
        'phone',
        'title',
        'description',
        'status',
        'sent_at',
        'error_message',
        'response_code',
        'api_response',
        'raw_response',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'api_response' => 'array',
    ];

    public function smsMessage(): BelongsTo
    {
        return $this->belongsTo(SmsMessage::class);
    }
}
