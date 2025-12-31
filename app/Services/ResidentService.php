<?php

namespace App\Services;

use App\Models\Resident;
use Illuminate\Support\Facades\Log;

class ResidentService
{
    /**
     * دریافت تمام اقامت‌گران از دیتابیس به فرمت مشابه API
     */
    public function getAllResidents()
    {
        try {
            $residents = Resident::orderBy('unit_code')
                ->orderBy('room_name')
                ->orderBy('bed_name')
                ->get();

            // گروه‌بندی بر اساس unit
            $units = [];
            
            foreach ($residents as $resident) {
                $unitId = $resident->unit_id;
                $unitCode = $resident->unit_code ?? $unitId;
                
                if (!isset($units[$unitCode])) {
                    $units[$unitCode] = [
                        'unit' => [
                            'id' => $resident->unit_id,
                            'name' => $resident->unit_name,
                            'code' => $resident->unit_code,
                        ],
                        'rooms' => [],
                    ];
                }
                
                $roomId = $resident->room_id;
                $roomName = $resident->room_name ?? $roomId;
                
                if (!isset($units[$unitCode]['rooms'][$roomName])) {
                    $units[$unitCode]['rooms'][$roomName] = [
                        'id' => $resident->room_id,
                        'name' => $resident->room_name,
                    ];
                    $units[$unitCode]['rooms'][$roomName]['beds'] = [];
                }
                
                $bedId = $resident->bed_id;
                $bedName = $resident->bed_name ?? $bedId;
                
                if (!isset($units[$unitCode]['rooms'][$roomName]['beds'][$bedName])) {
                    $units[$unitCode]['rooms'][$roomName]['beds'][$bedName] = [
                        'id' => $resident->bed_id,
                        'name' => $resident->bed_name,
                    ];
                }
                
                // اضافه کردن resident به bed - با تمام فیلدها (دقیقاً مثل API)
                $residentData = [
                    'id' => $resident->resident_id,
                    'full_name' => $resident->resident_full_name,
                    'name' => $resident->resident_full_name,
                    'phone' => $resident->resident_phone,
                    'age' => $resident->resident_age,
                    'birth_date' => $resident->resident_birth_date?->format('Y-m-d'),
                    'job' => $resident->resident_job,
                    'referral_source' => $resident->resident_referral_source,
                    'form' => $resident->resident_form,
                    'document' => $resident->resident_document,
                    'rent' => $resident->resident_rent,
                    'trust' => $resident->resident_trust,
                    'created_at' => $resident->resident_created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $resident->resident_updated_at?->format('Y-m-d H:i:s'),
                    'deleted_at' => $resident->resident_deleted_at?->format('Y-m-d H:i:s'),
                ];
                
                // اضافه کردن تاریخ‌های قرارداد (دقیقاً مثل API)
                if ($resident->contract_start_date) {
                    $residentData['start_date'] = $resident->contract_start_date->format('Y-m-d');
                    $residentData['contract_start_date'] = $resident->contract_start_date->format('Y-m-d');
                    $residentData['contract_start_date_jalali'] = $resident->contract_start_date_jalali;
                }
                if ($resident->contract_end_date) {
                    $residentData['end_date'] = $resident->contract_end_date->format('Y-m-d');
                    $residentData['contract_end_date'] = $resident->contract_end_date->format('Y-m-d');
                    $residentData['contract_end_date_jalali'] = $resident->contract_end_date_jalali;
                }
                if ($resident->contract_payment_date) {
                    $residentData['payment_date'] = $resident->contract_payment_date->format('Y-m-d');
                    $residentData['contract_payment_date'] = $resident->contract_payment_date->format('Y-m-d');
                    $residentData['contract_payment_date_jalali'] = $resident->contract_payment_date_jalali;
                }
                
                // اضافه کردن فیلدهای contract دیگر
                $residentData['contract_id'] = $resident->contract_id;
                $residentData['contract_resident_id'] = $resident->contract_resident_id;
                $residentData['contract_bed_id'] = $resident->contract_bed_id;
                $residentData['contract_state'] = $resident->contract_state;
                $residentData['contract_created_at'] = $resident->contract_created_at?->format('Y-m-d H:i:s');
                $residentData['contract_updated_at'] = $resident->contract_updated_at?->format('Y-m-d H:i:s');
                $residentData['contract_deleted_at'] = $resident->contract_deleted_at?->format('Y-m-d H:i:s');
                
                // اضافه کردن فیلدهای notes (JSON)
                $residentData['notes'] = $resident->notes;
                
                $units[$unitCode]['rooms'][$roomName]['beds'][$bedName]['resident'] = $residentData;
            }
            
            // تبدیل به آرایه عددی و مرتب‌سازی
            $result = array_values($units);
            foreach ($result as &$unit) {
                $unit['rooms'] = array_values($unit['rooms']);
                foreach ($unit['rooms'] as &$room) {
                    $room['beds'] = array_values($room['beds']);
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error getting residents from database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }
    
    /**
     * دریافت اطلاعات یک اقامت‌گر بر اساس ID
     */
    public function getResidentById($residentId)
    {
        try {
            $resident = Resident::where('resident_id', $residentId)->first();
            
            if (!$resident) {
                return null;
            }
            
            // آماده‌سازی داده‌های resident با تمام فیلدها (دقیقاً مثل API)
            $residentDataArray = [
                'id' => $resident->resident_id,
                'full_name' => $resident->resident_full_name,
                'name' => $resident->resident_full_name,
                'phone' => $resident->resident_phone,
                'age' => $resident->resident_age,
                'birth_date' => $resident->resident_birth_date?->format('Y-m-d'),
                'job' => $resident->resident_job,
                'referral_source' => $resident->resident_referral_source,
                'form' => $resident->resident_form,
                'document' => $resident->resident_document,
                'rent' => $resident->resident_rent,
                'trust' => $resident->resident_trust,
                'created_at' => $resident->resident_created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $resident->resident_updated_at?->format('Y-m-d H:i:s'),
                'deleted_at' => $resident->resident_deleted_at?->format('Y-m-d H:i:s'),
            ];
            
            // اضافه کردن تاریخ‌های قرارداد (دقیقاً مثل API)
            if ($resident->contract_start_date) {
                $residentDataArray['start_date'] = $resident->contract_start_date->format('Y-m-d');
                $residentDataArray['contract_start_date'] = $resident->contract_start_date->format('Y-m-d');
                $residentDataArray['contract_start_date_jalali'] = $resident->contract_start_date_jalali;
            }
            if ($resident->contract_end_date) {
                $residentDataArray['end_date'] = $resident->contract_end_date->format('Y-m-d');
                $residentDataArray['contract_end_date'] = $resident->contract_end_date->format('Y-m-d');
                $residentDataArray['contract_end_date_jalali'] = $resident->contract_end_date_jalali;
            }
            if ($resident->contract_payment_date) {
                $residentDataArray['payment_date'] = $resident->contract_payment_date->format('Y-m-d');
                $residentDataArray['contract_payment_date'] = $resident->contract_payment_date->format('Y-m-d');
                $residentDataArray['contract_payment_date_jalali'] = $resident->contract_payment_date_jalali;
            }
            
            // اضافه کردن فیلدهای contract دیگر
            $residentDataArray['contract_id'] = $resident->contract_id;
            $residentDataArray['contract_resident_id'] = $resident->contract_resident_id;
            $residentDataArray['contract_bed_id'] = $resident->contract_bed_id;
            $residentDataArray['contract_state'] = $resident->contract_state;
            $residentDataArray['contract_created_at'] = $resident->contract_created_at?->format('Y-m-d H:i:s');
            $residentDataArray['contract_updated_at'] = $resident->contract_updated_at?->format('Y-m-d H:i:s');
            $residentDataArray['contract_deleted_at'] = $resident->contract_deleted_at?->format('Y-m-d H:i:s');
            
            // اضافه کردن فیلدهای notes (JSON)
            $residentDataArray['notes'] = $resident->notes;
            
            return [
                'resident' => $residentDataArray,
                'unit' => [
                    'id' => $resident->unit_id,
                    'name' => $resident->unit_name,
                    'code' => $resident->unit_code,
                ],
                'room' => [
                    'id' => $resident->room_id,
                    'name' => $resident->room_name,
                ],
                'bed' => [
                    'id' => $resident->bed_id,
                    'name' => $resident->bed_name,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting resident from database', [
                'resident_id' => $residentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}