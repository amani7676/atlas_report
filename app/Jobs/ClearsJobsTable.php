<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait ClearsJobsTable
{
    /**
     * Clear the jobs table completely and reset auto-increment
     */
    protected function clearJobsTable(): void
    {
        try {
            $deletedCount = DB::table('jobs')->delete();
            DB::table('job_batches')->delete();
            DB::table('failed_jobs')->delete();
            
            // ریست کردن auto-increment ID برای شروع از 1
            DB::statement('ALTER TABLE jobs AUTO_INCREMENT = 1');
            
            if ($deletedCount > 0) {
                Log::info("Jobs table cleared and ID reset", [
                    'job_class' => static::class,
                    'deleted_jobs' => $deletedCount
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to clear jobs table: " . $e->getMessage());
        }
    }

    /**
     * Clear jobs table and execute the job
     */
    protected function executeWithCleanup(callable $callback): void
    {
        // پاک کردن جدول قبل از اجرا
        $this->clearJobsTable();
        
        // اجرای منطق اصلی
        $callback();
        
        // پاک کردن مجدد بعد از اجرا (برای اطمینان)
        $this->clearJobsTable();
    }
}
