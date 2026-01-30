<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // حذف فوری job بعد از اجرای موفق
        Queue::after(function (JobProcessed $event) {
            try {
                // حذف فوری job از جدول
                $this->deleteJobImmediately($event);
                
            } catch (\Exception $e) {
                Log::error('Error deleting job after processing: ' . $e->getMessage());
            }
        });

        // مدیریت job های ناموفق
        Queue::failing(function (JobFailed $event) {
            Log::warning('Job failed', [
                'job' => get_class($event->job),
                'exception' => $event->exception->getMessage()
            ]);
        });
    }

    /**
     * Delete job immediately from database
     */
    private function deleteJobImmediately(JobProcessed $event)
    {
        $job = $event->job;
        $payload = json_decode($job->getRawBody(), true);
        
        if (isset($payload['id'])) {
            // حذف job از جدول با استفاده از ID
            $deleted = DB::table('jobs')
                ->where('id', $payload['id'])
                ->delete();

            if ($deleted) {
                Log::info('Job deleted immediately after processing', [
                    'job_id' => $payload['id'],
                    'display_name' => $job->resolveName(),
                    'queue' => $job->getQueue()
                ]);
            }
        }
    }
}
