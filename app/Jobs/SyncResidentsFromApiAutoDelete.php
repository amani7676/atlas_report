<?php

namespace App\Jobs;

use App\Models\Resident;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncResidentsFromApiAutoDelete extends BaseAutoDeleteJob
{
    /**
     * Execute the main job logic.
     */
    protected function execute()
    {
        Log::info('Starting residents sync from API...');

        try {
            // دریافت URL از تنظیمات
            $settings = \App\Models\Settings::getSettings();
            $apiUrl = $settings->api_url ?? 'http://atlas2.test/api/residents';

            // ارسال درخواست به API
            $response = Http::timeout(30)->get($apiUrl);

            if (!$response->successful()) {
                throw new \Exception("API request failed: " . $response->status());
            }

            $residents = $response->json();
            $updatedCount = 0;
            $createdCount = 0;

            // پردازش داده‌ها
            foreach ($residents as $residentData) {
                $resident = Resident::updateOrCreate(
                    ['resident_id' => $residentData['resident_id']],
                    [
                        'contract_id' => $residentData['contract_id'] ?? null,
                        'unit_id' => $residentData['unit_id'] ?? null,
                        'unit_name' => $residentData['unit_name'] ?? null,
                        'unit_code' => $residentData['unit_code'] ?? null,
                        'unit_desc' => $residentData['unit_desc'] ?? null,
                        'unit_created_at' => $residentData['unit_created_at'] ?? null,
                        'unit_updated_at' => $residentData['unit_updated_at'] ?? null,
                        'room_id' => $residentData['room_id'] ?? null,
                        'room_name' => $residentData['room_name'] ?? null,
                        'room_code' => $residentData['room_code'] ?? null,
                        'room_unit_id' => $residentData['room_unit_id'] ?? null,
                        'room_bed_count' => $residentData['room_bed_count'] ?? null,
                        'room_desc' => $residentData['room_desc'] ?? null,
                        'room_type' => $residentData['room_type'] ?? null,
                        'room_created_at' => $residentData['room_created_at'] ?? null,
                        'room_updated_at' => $residentData['room_updated_at'] ?? null,
                        'bed_id' => $residentData['bed_id'] ?? null,
                        'bed_name' => $residentData['bed_name'] ?? null,
                        'bed_code' => $residentData['bed_code'] ?? null,
                        'bed_room_id' => $residentData['bed_room_id'] ?? null,
                        'bed_state_ratio_resident' => $residentData['bed_state_ratio_resident'] ?? null,
                        'bed_state' => $residentData['bed_state'] ?? null,
                        'bed_desc' => $residentData['bed_desc'] ?? null,
                        'bed_created_at' => $residentData['bed_created_at'] ?? null,
                        'bed_updated_at' => $residentData['bed_updated_at'] ?? null,
                        'contract_resident_id' => $residentData['contract_resident_id'] ?? null,
                        'contract_payment_date' => $residentData['contract_payment_date'] ?? null,
                        'contract_payment_date_jalali' => $residentData['contract_payment_date_jalali'] ?? null,
                        'contract_bed_id' => $residentData['contract_bed_id'] ?? null,
                        'contract_state' => $residentData['contract_state'] ?? null,
                        'contract_start_date' => $residentData['contract_start_date'] ?? null,
                        'contract_start_date_jalali' => $residentData['contract_start_date_jalali'] ?? null,
                        'contract_end_date' => $residentData['contract_end_date'] ?? null,
                        'contract_end_date_jalali' => $residentData['contract_end_date_jalali'] ?? null,
                        'contract_created_at' => $residentData['contract_created_at'] ?? null,
                        'contract_updated_at' => $residentData['contract_updated_at'] ?? null,
                        'contract_deleted_at' => $residentData['contract_deleted_at'] ?? null,
                        'resident_full_name' => $residentData['resident_full_name'],
                        'resident_phone' => $residentData['resident_phone'] ?? null,
                        'resident_age' => $residentData['resident_age'] ?? null,
                        'resident_birth_date' => $residentData['resident_birth_date'] ?? null,
                        'resident_job' => $residentData['resident_job'] ?? null,
                        'resident_referral_source' => $residentData['resident_referral_source'] ?? null,
                        'resident_form' => $residentData['resident_form'] ?? false,
                        'resident_document' => $residentData['resident_document'] ?? false,
                        'resident_rent' => $residentData['resident_rent'] ?? false,
                        'resident_trust' => $residentData['resident_trust'] ?? false,
                        'resident_created_at' => $residentData['resident_created_at'] ?? null,
                        'resident_updated_at' => $residentData['resident_updated_at'] ?? null,
                        'resident_deleted_at' => $residentData['resident_deleted_at'] ?? null,
                        'notes' => $residentData['notes'] ?? null,
                        'delay' => $residentData['delay'] ?? 0,
                        'last_synced_at' => now(),
                    ]
                );

                if ($resident->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }
            }

            Log::info("Residents sync completed successfully", [
                'created' => $createdCount,
                'updated' => $updatedCount,
                'total' => count($residents)
            ]);

        } catch (\Exception $e) {
            Log::error("Residents sync failed: " . $e->getMessage());
            throw $e;
        }
    }
}
