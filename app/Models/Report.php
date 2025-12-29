<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'category_id',
        'type',
        'title',
        'description',
        'negative_score',
        'increase_coefficient',
        'page_number'
    ];

    protected $casts = [
        'increase_coefficient' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    public function smsMessages()
    {
        return $this->belongsToMany(SmsMessage::class, 'report_sms_message')
            ->withPivot('send_type')
            ->withTimestamps();
    }
    
    public function patterns()
    {
        return $this->belongsToMany(Pattern::class, 'report_pattern')
            ->withPivot('sort_order', 'is_active')
            ->withTimestamps()
            ->orderBy('report_pattern.sort_order');
    }
    
    public function activePatterns()
    {
        return $this->patterns()->wherePivot('is_active', true);
    }
}
