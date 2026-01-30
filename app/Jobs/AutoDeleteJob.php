<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class AutoDeleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 1;

    /**
     * Delete the job after it has processed successfully.
     */
    public $deleteWhenComplete = true;

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // اجرای منطق اصلی
            $this->execute();
            
            // حذف فوری از دیتابیس
            $this->deleteFromDatabase();
            
            Log::info('Job executed and deleted successfully', [
                'job' => static::class
            ]);
            
        } catch (\Exception $e) {
            Log::error('Job failed: ' . $e->getMessage(), [
                'job' => static::class
            ]);
            throw $e;
        }
    }

    /**
     * Execute the main job logic - should be implemented by child classes
     */
    abstract protected function execute();

    /**
     * Delete the job from database immediately
     */
    private function deleteFromDatabase()
    {
        try {
            // حذف job از جدول با استفاده از نام کلاس
            $deleted = DB::table('jobs')
                ->where('payload', 'like', '%' . static::class . '%')
                ->delete();

            if ($deleted > 0) {
                Log::info("Job deleted from database: {$deleted} records");
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete job from database: ' . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Job failed permanently', [
            'job' => static::class,
            'error' => $exception->getMessage()
        ]);
    }
}
