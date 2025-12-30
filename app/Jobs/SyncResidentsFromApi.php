<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Resident;
use Illuminate\Support\Facades\Cache;

class SyncResidentsFromApi implements ShouldQueue
{
    use Queueable;
    
    /**
     * Execute the job synchronously (not in queue)
     */
    public $tries = 1;
    public $timeout = 300;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $apiUrl = 'http://atlas2.test/api/residents';
            
            Log::info('Starting residents sync from API', ['url' => $apiUrl]);
            
            $response = Http::timeout(60)->get($apiUrl);
            
            if (!$response->successful()) {
                Log::error('Failed to fetch residents from API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return;
            }
            
            $units = $response->json();
            
            if (empty($units) || !is_array($units)) {
                Log::warning('No units found in API response or invalid format', [
                    'response_type' => gettype($units),
                    'response_preview' => is_array($units) ? 'array with ' . count($units) . ' items' : substr(json_encode($units), 0, 200),
                ]);
                return;
            }
            
            $syncedCount = 0;
            $createdCount = 0;
            $updatedCount = 0;
            
            Log::info('Processing units', ['count' => count($units)]);
            
            foreach ($units as $unit) {
                $unitData = $unit['unit'] ?? null;
                $rooms = $unit['rooms'] ?? [];
                
                foreach ($rooms as $room) {
                    $roomData = $room;
                    $beds = $room['beds'] ?? [];
                    
                    foreach ($beds as $bed) {
                        $bedData = $bed;
                        $resident = $bed['resident'] ?? null;
                        
                        if (!$resident || !isset($resident['id'])) {
                            continue;
                        }
                        
                        $residentId = $resident['id'];
                        
                        // استخراج اطلاعات contract - تمام فیلدهای contract را با نام یکسان در resident_data ذخیره می‌کنیم
                        $contract = $resident['contract'] ?? [];
                        
                        // ایجاد mergedResidentData که شامل تمام فیلدهای resident و contract با نام یکسان است
                        $mergedResidentData = $resident;
                        
                        // اگر contract به صورت جداگانه وجود دارد، تمام فیلدهای آن را با prefix contract_ به resident_data اضافه می‌کنیم
                        if (!empty($contract) && is_array($contract)) {
                            foreach ($contract as $key => $value) {
                                // نام یکسان برای فیلدهای contract: contract_[field_name]
                                $contractFieldName = 'contract_' . $key;
                                $mergedResidentData[$contractFieldName] = $value;
                            }
                            // همچنین contract object کامل را هم نگه می‌داریم
                            $mergedResidentData['contract'] = $contract;
                        }
                        
                        // استخراج تاریخ‌های قرارداد با نام یکسان - اولویت با فیلدهای با prefix contract_
                        $contractStartDate = $this->parseDate(
                            $mergedResidentData['contract_start_date'] ?? 
                            $resident['contract_start_date'] ?? 
                            $resident['contract']['start_date'] ?? 
                            $resident['start_date'] ?? 
                            $contract['start_date'] ?? 
                            null
                        );
                        
                        $contractEndDate = $this->parseDate(
                            $mergedResidentData['contract_end_date'] ?? 
                            $resident['contract_end_date'] ?? 
                            $resident['contract']['end_date'] ?? 
                            $resident['end_date'] ?? 
                            $contract['end_date'] ?? 
                            null
                        );
                        
                        $contractExpiryDate = $this->parseDate(
                            $mergedResidentData['contract_expiry_date'] ?? 
                            $resident['contract_expiry_date'] ?? 
                            $resident['contract']['expiry_date'] ?? 
                            $resident['expiry_date'] ?? 
                            $contract['expiry_date'] ?? 
                            null
                        );
                        
                        // اطمینان از اینکه تاریخ‌های استخراج شده در mergedResidentData با نام یکسان ذخیره شوند
                        if ($contractStartDate) {
                            $mergedResidentData['contract_start_date'] = $contractStartDate;
                        }
                        if ($contractEndDate) {
                            $mergedResidentData['contract_end_date'] = $contractEndDate;
                        }
                        if ($contractExpiryDate) {
                            $mergedResidentData['contract_expiry_date'] = $contractExpiryDate;
                        }
                        
                        // استخراج اطلاعات اصلی
                        $data = [
                            'resident_id' => $residentId,
                            'full_name' => $resident['full_name'] ?? $resident['name'] ?? null,
                            'phone' => $resident['phone'] ?? null,
                            'national_id' => $resident['national_id'] ?? $resident['national_code'] ?? null,
                            'national_code' => $resident['national_code'] ?? $resident['national_id'] ?? null,
                            'unit_id' => $unitData['id'] ?? null,
                            'unit_name' => $unitData['name'] ?? null,
                            'unit_code' => $unitData['code'] ?? null,
                            'room_id' => $roomData['id'] ?? null,
                            'room_name' => $roomData['name'] ?? null,
                            'bed_id' => $bedData['id'] ?? null,
                            'bed_name' => $bedData['name'] ?? null,
                            'contract_start_date' => $contractStartDate,
                            'contract_end_date' => $contractEndDate,
                            'contract_expiry_date' => $contractExpiryDate,
                            // ذخیره تمام داده‌های resident و contract با نام یکسان در resident_data
                            'resident_data' => $mergedResidentData,
                            'unit_data' => $unitData,
                            'room_data' => $roomData,
                            'bed_data' => $bedData,
                            'last_synced_at' => now(),
                        ];
                        
                        // ذخیره یا به‌روزرسانی
                        try {
                            $existing = Resident::where('resident_id', $residentId)->first();
                            
                            if ($existing) {
                                $existing->update($data);
                                $updatedCount++;
                            } else {
                                Resident::create($data);
                                $createdCount++;
                            }
                            
                            $syncedCount++;
                        } catch (\Exception $e) {
                            Log::error('Error saving resident', [
                                'resident_id' => $residentId,
                                'error' => $e->getMessage(),
                                'data_keys' => array_keys($data),
                            ]);
                        }
                    }
                }
            }
            
            // پاک کردن cache
            Cache::forget('residents_api_data');
            
            // ذخیره اطلاعات آخرین sync
            $syncData = [
                'time' => now()->format('Y-m-d H:i:s'),
                'synced_count' => $syncedCount,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'message' => "دیتابیس اقامت‌گران به‌روزرسانی شد. تعداد: {$syncedCount}",
            ];
            
            Cache::put('residents_last_sync', $syncData, now()->addDays(7)); // 7 روز cache
            
            Log::info('Residents sync completed', [
                'synced' => $syncedCount,
                'created' => $createdCount,
                'updated' => $updatedCount,
            ]);
            
            // ارسال Event برای notification
            event(new \App\Events\ResidentsSynced($syncedCount, $createdCount, $updatedCount));
            
        } catch (\Exception $e) {
            Log::error('Error syncing residents from API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    
    /**
     * تبدیل تاریخ به فرمت قابل ذخیره
     */
    protected function parseDate($date)
    {
        if (!$date) {
            return null;
        }
        
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
