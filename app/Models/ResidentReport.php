<?php

namespace App\Models;

use App\Events\ResidentReportCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentReport extends Model
{
    protected $fillable = [
        'report_id',
        'resident_id',
        'unit_id',
        'room_id',
        'bed_id',
        'notes',
        'resident_name',
        'unit_name',
        'room_name',
        'bed_name',
        'phone'  // افزودن فیلد تلفن

    ];

    protected $casts = [
        'unit_id' => 'integer',
        'room_id' => 'integer',
        'bed_id' => 'integer',
        'resident_id' => 'integer',
    ];

    protected static function booted()
    {
        static::created(function ($residentReport) {
            event(new ResidentReportCreated($residentReport));
        });
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function category()
    {
        return $this->report->category();
    }
}
