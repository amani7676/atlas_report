<?php

namespace App\Models;

use App\Traits\TriggersAutoSms;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use TriggersAutoSms;
    
    protected $fillable = [
        'resident_id',
        'contract_id',
        
        // فیلدهای unit (دقیقاً مثل API)
        'unit_id',
        'unit_name',
        'unit_code',
        'unit_desc',
        'unit_created_at',
        'unit_updated_at',
        
        // فیلدهای room (دقیقاً مثل API)
        'room_id',
        'room_name',
        'room_code',
        'room_unit_id',
        'room_bed_count',
        'room_desc',
        'room_type',
        'room_created_at',
        'room_updated_at',
        
        // فیلدهای bed (دقیقاً مثل API)
        'bed_id',
        'bed_name',
        'bed_code',
        'bed_room_id',
        'bed_state_ratio_resident',
        'bed_state',
        'bed_desc',
        'bed_created_at',
        'bed_updated_at',
        
        // فیلدهای contract (دقیقاً مثل API)
        'contract_resident_id',
        'contract_payment_date',
        'contract_payment_date_jalali',
        'contract_bed_id',
        'contract_state',
        'contract_start_date',
        'contract_start_date_jalali',
        'contract_end_date',
        'contract_end_date_jalali',
        'contract_created_at',
        'contract_updated_at',
        'contract_deleted_at',
        
        // فیلدهای resident (دقیقاً مثل API)
        'resident_full_name',
        'resident_phone',
        'resident_age',
        'resident_birth_date',
        'resident_job',
        'resident_referral_source',
        'resident_form',
        'resident_document',
        'resident_rent',
        'resident_trust',
        'resident_created_at',
        'resident_updated_at',
        'resident_deleted_at',
        
        // فیلد notes (JSON)
        'notes',
        
        'last_synced_at',
    ];

    /**
     * رابطه با بخشودگی‌های اقامت‌گر
     */
    public function grants()
    {
        return $this->hasMany(ResidentGrant::class, 'resident_id', 'resident_id');
    }

    protected $casts = [
        'resident_birth_date' => 'date',
        'contract_payment_date' => 'datetime',
        'contract_start_date' => 'datetime',
        'contract_end_date' => 'datetime',
        'contract_created_at' => 'datetime',
        'contract_updated_at' => 'datetime',
        'contract_deleted_at' => 'datetime',
        'unit_created_at' => 'datetime',
        'unit_updated_at' => 'datetime',
        'room_created_at' => 'datetime',
        'room_updated_at' => 'datetime',
        'bed_created_at' => 'datetime',
        'bed_updated_at' => 'datetime',
        'resident_created_at' => 'datetime',
        'resident_updated_at' => 'datetime',
        'resident_deleted_at' => 'datetime',
        'resident_form' => 'boolean',
        'resident_document' => 'boolean',
        'resident_rent' => 'boolean',
        'resident_trust' => 'boolean',
        'notes' => 'array',
        'last_synced_at' => 'datetime',
    ];
}
