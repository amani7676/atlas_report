<?php

namespace App\Models;

use App\Traits\TriggersAutoSms;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use TriggersAutoSms;
    protected $fillable = [
        'resident_id',
        'full_name',
        'phone',
        'national_id',
        'national_code',
        'unit_id',
        'unit_name',
        'unit_code',
        'room_id',
        'room_name',
        'bed_id',
        'bed_name',
        // فیلدهای contract با نام یکسان (contract_start_date, contract_end_date, contract_expiry_date)
        'contract_start_date',
        'contract_end_date',
        'contract_expiry_date',
        // resident_data شامل تمام فیلدهای resident و contract با نام یکسان است
        // فیلدهای contract با prefix contract_ ذخیره می‌شوند (مثل contract_start_date، contract_end_date و غیره)
        'resident_data',
        'unit_data',
        'room_data',
        'bed_data',
        'extra_data',
        'last_synced_at',
    ];

    protected $casts = [
        'resident_data' => 'array',
        'unit_data' => 'array',
        'room_data' => 'array',
        'bed_data' => 'array',
        'extra_data' => 'array',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'contract_expiry_date' => 'date',
        'last_synced_at' => 'datetime',
    ];
}
