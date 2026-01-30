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
                        'resident_full_name' => $residentData['resident_full_name'],
                        'resident_phone' => $residentData['resident_phone'] ?? null,
                        'resident_age' => $residentData['resident_age'] ?? null,
                        'resident_birth_date' => $residentData['resident_birth_date'] ?? null,
                        'resident_job' => $residentData['resident_job'] ?? null,
                        'resident_referral_source' => $residentData['resident_referral_source'] ?? null,
                        'resident_form' => $residentData['resident_form'] ?? false,
                        'resident_document' => $residentData['resident_document'] ?? false,
                        'unit_name' => $residentData['unit_name'] ?? null,
                        'unit_code' => $residentData['unit_code'] ?? null,
                        'room_name' => $residentData['room_name'] ?? null,
                        'room_code' => $residentData['room_code'] ?? null,
                        'bed_name' => $residentData['bed_name'] ?? null,
                        'bed_code' => $residentData['bed_code'] ?? null,
                        'contract_start_date' => $residentData['contract_start_date'] ?? null,
                        'contract_end_date' => $residentData['contract_end_date'] ?? null,
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
