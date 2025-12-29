<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ResidentApiService
{
    protected $apiUrl = 'http://atlas2.test/api/residents';
    protected $cacheKey = 'residents_api_data';
    protected $cacheTime = 300; // 5 minutes

    /**
     * دریافت اطلاعات اقامت‌گر از API بر اساس ID
     * @deprecated استفاده از ResidentService::getResidentById() به جای این متد
     */
    public function getResidentById($residentId)
    {
        try {
            $residentService = new \App\Services\ResidentService();
            return $residentService->getResidentById($residentId);
        } catch (\Exception $e) {
            Log::error('Error getting resident from database', [
                'resident_id' => $residentId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * دریافت همه اقامت‌گران از API
     * @deprecated استفاده از ResidentService::getAllResidents() به جای این متد
     */
    public function getAllResidents()
    {
        try {
            // استفاده از دیتابیس به جای API
            $residentService = new \App\Services\ResidentService();
            return $residentService->getAllResidents();
        } catch (\Exception $e) {
            Log::error('Error fetching residents from database', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * به‌روزرسانی اطلاعات اقامت‌گر در دیتابیس با اطلاعات API
     */
    public function syncResidentData($model, $residentId = null)
    {
        $id = $residentId ?? $model->resident_id;
        
        if (!$id) {
            return $model;
        }

        try {
            $apiData = $this->getResidentById($id);
            
            if (!$apiData) {
                return $model;
            }

            $resident = $apiData['resident'];
            $unit = $apiData['unit'];
            $room = $apiData['room'];
            $bed = $apiData['bed'];

            // به‌روزرسانی فیلدهای موجود در مدل
            $updates = [];
            
            // بررسی وجود فیلد در مدل با استفاده از fillable
            $fillable = $model->getFillable();
            
            if (in_array('resident_name', $fillable)) {
                $newName = $resident['full_name'] ?? $resident['name'] ?? null;
                if ($newName && $model->resident_name != $newName) {
                    $updates['resident_name'] = $newName;
                }
            }
            
            if (in_array('phone', $fillable)) {
                $newPhone = $resident['phone'] ?? null;
                if ($newPhone && $model->phone != $newPhone) {
                    $updates['phone'] = $newPhone;
                }
            }
            
            if (in_array('unit_name', $fillable) && $unit) {
                $newUnitName = $unit['name'] ?? null;
                if ($newUnitName && $model->unit_name != $newUnitName) {
                    $updates['unit_name'] = $newUnitName;
                }
            }
            
            if (in_array('room_name', $fillable) && $room) {
                $newRoomName = $room['name'] ?? null;
                if ($newRoomName && $model->room_name != $newRoomName) {
                    $updates['room_name'] = $newRoomName;
                }
            }
            
            if (in_array('bed_name', $fillable) && $bed) {
                $newBedName = $bed['name'] ?? null;
                if ($newBedName && $model->bed_name != $newBedName) {
                    $updates['bed_name'] = $newBedName;
                }
            }

            // به‌روزرسانی فقط اگر تغییری وجود داشته باشد
            if (!empty($updates)) {
                $model->update($updates);
                Log::debug('Resident data synced', [
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'resident_id' => $id,
                    'updates' => $updates
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error syncing resident data', [
                'model_type' => get_class($model),
                'model_id' => $model->id ?? null,
                'resident_id' => $id,
                'error' => $e->getMessage()
            ]);
        }

        return $model;
    }

    /**
     * پاک کردن cache
     */
    public function clearCache()
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * دریافت اطلاعات کامل اقامت‌گر برای استفاده در متغیرها
     */
    public function getResidentDataForVariables($residentId)
    {
        $apiData = $this->getResidentById($residentId);
        
        if (!$apiData) {
            return null;
        }

        $resident = $apiData['resident'];
        $unit = $apiData['unit'];
        $room = $apiData['room'];
        $bed = $apiData['bed'];

        return [
            'id' => $resident['id'] ?? null,
            'name' => $resident['full_name'] ?? $resident['name'] ?? '',
            'phone' => $resident['phone'] ?? '',
            'unit_name' => $unit['name'] ?? '',
            'unit_code' => $unit['code'] ?? '',
            'room_name' => $room['name'] ?? '',
            'bed_name' => $bed['name'] ?? '',
            'national_id' => $resident['national_id'] ?? $resident['national_code'] ?? '',
            'contract_start_date' => $resident['contract_start_date'] ?? $resident['start_date'] ?? $resident['contract_start'] ?? null,
            'contract_end_date' => $resident['contract_end_date'] ?? $resident['end_date'] ?? $resident['contract_end'] ?? null,
            'contract_expiry_date' => $resident['contract_expiry_date'] ?? $resident['expiry_date'] ?? $resident['contract_expiry'] ?? null,
        ];
    }
}

