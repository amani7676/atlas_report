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
                        ] + ($resident->unit_data ?? []),
                        'rooms' => [],
                    ];
                }
                
                $roomId = $resident->room_id;
                $roomName = $resident->room_name ?? $roomId;
                
                if (!isset($units[$unitCode]['rooms'][$roomName])) {
                    $units[$unitCode]['rooms'][$roomName] = [
                        'id' => $resident->room_id,
                        'name' => $resident->room_name,
                    ] + ($resident->room_data ?? []);
                    $units[$unitCode]['rooms'][$roomName]['beds'] = [];
                }
                
                $bedId = $resident->bed_id;
                $bedName = $resident->bed_name ?? $bedId;
                
                if (!isset($units[$unitCode]['rooms'][$roomName]['beds'][$bedName])) {
                    $units[$unitCode]['rooms'][$roomName]['beds'][$bedName] = [
                        'id' => $resident->bed_id,
                        'name' => $resident->bed_name,
                    ] + ($resident->bed_data ?? []);
                }
                
                // اضافه کردن resident به bed
                $residentData = [
                    'id' => $resident->resident_id,
                    'full_name' => $resident->full_name,
                    'name' => $resident->full_name,
                    'phone' => $resident->phone,
                    'national_id' => $resident->national_id,
                    'national_code' => $resident->national_code,
                ] + ($resident->resident_data ?? []);
                
                // اضافه کردن تاریخ‌های قرارداد
                if ($resident->contract_start_date) {
                    $residentData['contract_start_date'] = $resident->contract_start_date->format('Y-m-d');
                }
                if ($resident->contract_end_date) {
                    $residentData['contract_end_date'] = $resident->contract_end_date->format('Y-m-d');
                }
                if ($resident->contract_expiry_date) {
                    $residentData['contract_expiry_date'] = $resident->contract_expiry_date->format('Y-m-d');
                }
                
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
            
            return [
                'resident' => [
                    'id' => $resident->resident_id,
                    'full_name' => $resident->full_name,
                    'name' => $resident->full_name,
                    'phone' => $resident->phone,
                    'national_id' => $resident->national_id,
                    'national_code' => $resident->national_code,
                ] + ($resident->resident_data ?? []),
                'unit' => [
                    'id' => $resident->unit_id,
                    'name' => $resident->unit_name,
                    'code' => $resident->unit_code,
                ] + ($resident->unit_data ?? []),
                'room' => [
                    'id' => $resident->room_id,
                    'name' => $resident->room_name,
                ] + ($resident->room_data ?? []),
                'bed' => [
                    'id' => $resident->bed_id,
                    'name' => $resident->bed_name,
                ] + ($resident->bed_data ?? []),
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





