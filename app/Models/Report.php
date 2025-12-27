<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'category_id',
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
}
