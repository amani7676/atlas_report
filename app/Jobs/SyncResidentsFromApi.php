<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Resident;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ResidentReport;
use App\Models\SmsMessageResident;
use App\Models\ResidentGrant;

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
            // دریافت URL از تنظیمات
            $settings = \App\Models\Settings::getSettings();
            $apiUrl = $settings->api_url ?? 'http://atlas2.test/api/residents';
            
            Log::info('Starting residents sync from API', ['url' => $apiUrl]);
            
            $response = Http::timeout(60)->get($apiUrl);
            
            if (!$response->successful()) {
                Log::error('Failed to fetch residents from API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return;
            }
            
            $residents = $response->json();
            
            // تبدیل به indexed array اگر associative باشد
            if (!empty($residents) && is_array($residents)) {
                $residents = array_values($residents);
            }
            
            if (empty($residents) || !is_array($residents)) {
                Log::warning('No residents found in API response or invalid format', [
                    'response_type' => gettype($residents),
                    'response_preview' => is_array($residents) ? 'array with ' . count($residents) . ' items' : substr(json_encode($residents), 0, 200),
                ]);
                return;
            }
            
            // استخراج resident_id های جدید از API
            $newResidentIds = [];
            foreach ($residents as $item) {
                if (isset($item['resident_id'])) {
                    $newResidentIds[] = $item['resident_id'];
                }
            }
            
            Log::info('Starting sync process', [
                'new_residents_count' => count($newResidentIds),
                'existing_count' => Resident::count()
            ]);
            
            // پیدا کردن resident_id هایی که در API نیستند (قبل از پاک کردن residents)
            $existingResidentIds = Resident::pluck('resident_id')->toArray();
            $removedResidentIds = array_diff($existingResidentIds, $newResidentIds);
            
            $deletedFromReports = 0;
            $deletedFromSms = 0;
            $deletedFromGrants = 0;
            
            // پاک کردن رکوردهای جداول وابسته برای resident_id هایی که در API نیستند
            if (!empty($removedResidentIds)) {
                // پیدا کردن id های دیتابیس برای resident_id هایی که حذف می‌شوند
                $removedDbIds = Resident::whereIn('resident_id', $removedResidentIds)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($removedDbIds)) {
                    // پاک کردن از resident_reports (با استفاده از id دیتابیس)
                    $deletedFromReports = ResidentReport::whereIn('resident_id', $removedDbIds)->delete();
                    
                    // پاک کردن از sms_message_residents (با استفاده از id دیتابیس)
                    $deletedFromSms = SmsMessageResident::whereIn('resident_id', $removedDbIds)->delete();
                    
                    // پاک کردن از resident_grants (با استفاده از resident_id از API)
                    if (Schema::hasTable('resident_grants')) {
                        $deletedFromGrants = ResidentGrant::whereIn('resident_id', $removedResidentIds)->delete();
                    }
                }
            }
            
            Log::info('Cleaned dependent tables for removed residents', [
                'removed_resident_ids_count' => count($removedResidentIds),
                'deleted_from_reports' => $deletedFromReports,
                'deleted_from_sms' => $deletedFromSms,
                'deleted_from_grants' => $deletedFromGrants,
            ]);
            
            // غیرفعال کردن foreign key checks موقتاً برای پاک کردن residents
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // پاک کردن همه داده‌های قبلی از جدول residents
            $deletedCount = Resident::query()->delete();
            
            // Reset AUTO_INCREMENT به 1 تا ID ها از 1 شروع شوند
            DB::statement('ALTER TABLE residents AUTO_INCREMENT = 1');
            
            // فعال کردن دوباره foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            Log::info('All existing residents deleted and AUTO_INCREMENT reset', [
                'deleted_count' => $deletedCount
            ]);
            
            $syncedCount = 0;
            $createdCount = 0;
            $updatedCount = 0;
            
            Log::info('Processing residents from API', ['count' => count($residents)]);
            
            // API به صورت flat است - هر ردیف یک resident کامل با تمام فیلدهاست
            foreach ($residents as $item) {
                if (!isset($item['resident_id'])) {
                    continue;
                }
                
                $residentId = $item['resident_id'];
                
                // تبدیل timestamp ها
                $data = [
                    'resident_id' => $residentId,
                    'contract_id' => $item['contract_id'] ?? null,
                    
                    // فیلدهای unit
                    'unit_id' => $item['unit_id'] ?? null,
                    'unit_name' => $item['unit_name'] ?? null,
                    'unit_code' => $item['unit_code'] ?? null,
                    'unit_desc' => $item['unit_desc'] ?? null,
                    'unit_created_at' => $this->parseDateTime($item['unit_created_at'] ?? null),
                    'unit_updated_at' => $this->parseDateTime($item['unit_updated_at'] ?? null),
                    
                    // فیلدهای room
                    'room_id' => $item['room_id'] ?? null,
                    'room_name' => $item['room_name'] ?? null,
                    'room_code' => $item['room_code'] ?? null,
                    'room_unit_id' => $item['room_unit_id'] ?? null,
                    'room_bed_count' => $item['room_bed_count'] ?? null,
                    'room_desc' => $item['room_desc'] ?? null,
                    'room_type' => $item['room_type'] ?? null,
                    'room_created_at' => $this->parseDateTime($item['room_created_at'] ?? null),
                    'room_updated_at' => $this->parseDateTime($item['room_updated_at'] ?? null),
                    
                    // فیلدهای bed
                    'bed_id' => $item['bed_id'] ?? null,
                    'bed_name' => $item['bed_name'] ?? null,
                    'bed_code' => $item['bed_code'] ?? null,
                    'bed_room_id' => $item['bed_room_id'] ?? null,
                    'bed_state_ratio_resident' => $item['bed_state_ratio_resident'] ?? null,
                    'bed_state' => $item['bed_state'] ?? null,
                    'bed_desc' => $item['bed_desc'] ?? null,
                    'bed_created_at' => $this->parseDateTime($item['bed_created_at'] ?? null),
                    'bed_updated_at' => $this->parseDateTime($item['bed_updated_at'] ?? null),
                    
                    // فیلدهای contract
                    'contract_resident_id' => $item['contract_resident_id'] ?? null,
                    'contract_payment_date' => $this->parseDateTime($item['contract_payment_date'] ?? null),
                    'contract_payment_date_jalali' => $item['contract_payment_date_jalali'] ?? null,
                    'contract_bed_id' => $item['contract_bed_id'] ?? null,
                    'contract_state' => $item['contract_state'] ?? null,
                    'contract_start_date' => $this->parseDateTime($item['contract_start_date'] ?? null),
                    'contract_start_date_jalali' => $item['contract_start_date_jalali'] ?? null,
                    'contract_end_date' => $this->parseDateTime($item['contract_end_date'] ?? null),
                    'contract_end_date_jalali' => $item['contract_end_date_jalali'] ?? null,
                    'contract_created_at' => $this->parseDateTime($item['contract_created_at'] ?? null),
                    'contract_updated_at' => $this->parseDateTime($item['contract_updated_at'] ?? null),
                    'contract_deleted_at' => $this->parseDateTime($item['contract_deleted_at'] ?? null),
                    
                    // فیلدهای resident
                    'resident_full_name' => $item['resident_full_name'] ?? null,
                    'resident_phone' => $item['resident_phone'] ?? null,
                    'resident_age' => $item['resident_age'] ?? null,
                    'resident_birth_date' => $this->parseDate($item['resident_birth_date'] ?? null),
                    'resident_job' => $item['resident_job'] ?? null,
                    'resident_referral_source' => $item['resident_referral_source'] ?? null,
                    'resident_form' => $item['resident_form'] ?? null,
                    'resident_document' => $item['resident_document'] ?? null,
                    'resident_rent' => $item['resident_rent'] ?? null,
                    'resident_trust' => $item['resident_trust'] ?? null,
                    'resident_created_at' => $this->parseDateTime($item['resident_created_at'] ?? null),
                    'resident_updated_at' => $this->parseDateTime($item['resident_updated_at'] ?? null),
                    'resident_deleted_at' => $this->parseDateTime($item['resident_deleted_at'] ?? null),
                    
                    // فیلد notes (JSON)
                    'notes' => $item['notes'] ?? null,
                    
                    'last_synced_at' => now(),
                ];
                
                // ایجاد رکورد جدید (چون همه داده‌های قبلی پاک شده‌اند)
                try {
                    Resident::create($data);
                    $createdCount++;
                    $syncedCount++;
                } catch (\Exception $e) {
                    Log::error('Error saving resident', [
                        'resident_id' => $residentId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
            
            // پاک کردن cache
            Cache::forget('residents_api_data');
            
            // ذخیره زمان آخرین sync برای Scheduler
            Cache::put('residents_last_sync_time', now(), now()->addDays(1));
            
            // ذخیره اطلاعات آخرین sync
            $syncData = [
                'time' => now()->format('Y-m-d H:i:s'),
                'synced_count' => $syncedCount,
                'created_count' => $createdCount,
                'updated_count' => 0, // همه رکوردها جدید هستند چون قبلاً پاک شده‌اند
                'deleted_count' => $deletedCount ?? 0,
                'deleted_from_reports' => $deletedFromReports ?? 0,
                'deleted_from_sms' => $deletedFromSms ?? 0,
                'deleted_from_grants' => $deletedFromGrants ?? 0,
                'message' => "دیتابیس اقامت‌گران به‌روزرسانی شد. تعداد: {$syncedCount} (پاک شده از residents: {$deletedCount}, از reports: {$deletedFromReports}, از sms: {$deletedFromSms})",
            ];
            
            Cache::put('residents_last_sync', $syncData, now()->addDays(7)); // 7 روز cache
            
            Log::info('Residents sync completed', [
                'synced' => $syncedCount,
                'created' => $createdCount,
                'updated' => $updatedCount,
                'deleted' => $deletedCount ?? 0,
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
    
    /**
     * تبدیل datetime به فرمت قابل ذخیره
     */
    protected function parseDateTime($datetime)
    {
        if (!$datetime) {
            return null;
        }
        
        try {
            return \Carbon\Carbon::parse($datetime);
        } catch (\Exception $e) {
            return null;
        }
    }
}