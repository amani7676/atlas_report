<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

abstract class BaseAutoDeleteJob implements ShouldQueue
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
     * Handle the job execution and auto-cleanup.
     */
    public function handle()
    {
        try {
            // اجرای منطق اصلی job
            $this->execute();

            // حذف فوری job از جدول بعد از اجرای موفق
            $this->autoCleanup();

        } catch (\Exception $e) {
            // در صورت خطا، لاگ کن و job به failed برود
            \Log::error('Job execution failed: ' . $e->getMessage(), [
                'job' => static::class,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Execute the main job logic.
     * This should be implemented by child classes.
     */
    abstract protected function execute();

    /**
     * Auto-cleanup the job after execution.
     */
    private function autoCleanup()
    {
        try {
            // حذف job از جدول jobs
            DB::table('jobs')
                ->where('queue', $this->queue)
                ->where('payload', 'like', '%' . static::class . '%')
                ->limit(1)
                ->delete();

            \Log::info('Job auto-deleted after execution', [
                'job' => static::class,
                'queue' => $this->queue
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to auto-delete job: ' . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        \Log::error('Job failed permanently', [
            'job' => static::class,
            'error' => $exception->getMessage()
        ]);
    }
}
