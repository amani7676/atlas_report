<?php

namespace App\Jobs;

use App\Models\WelcomeMessageLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ResendWelcomeMessage implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $welcomeLog;

    /**
     * Create a new job instance.
     */
    public function __construct(WelcomeMessageLog $welcomeLog)
    {
        $this->welcomeLog = $welcomeLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // پاک کردن کامل جدول jobs قبل از اجرا
        $this->clearJobsTable();
        
        try {
            $log = $this->welcomeLog->fresh();
            
            if ($log->status === 'sent') {
                Log::info('Welcome message already sent, skipping resend', [
                    'log_id' => $log->id
                ]);
                return;
            }

            // اینجا منطق ارسال مجدد پیام خوشامدگویی قرار می‌گیرد
            // برای سادگی، فقط وضعیت را به pending تغییر می‌دهیم
            $log->update([
                'status' => 'pending',
                'error_message' => null,
                'sent_at' => null,
            ]);

            Log::info('Welcome message queued for resend', [
                'log_id' => $log->id,
                'resident_name' => $log->resident_name
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ResendWelcomeMessage job', [
                'log_id' => $this->welcomeLog->id,
                'error' => $e->getMessage(),
            ]);

            $this->welcomeLog->update([
                'status' => 'failed',
                'error_message' => 'خطا در ارسال مجدد: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear the jobs table completely
     */
    private function clearJobsTable()
    {
        try {
            $deletedCount = DB::table('jobs')->delete();
            DB::table('job_batches')->delete();
            DB::table('failed_jobs')->delete();
            
            if ($deletedCount > 0) {
                Log::info("Jobs table cleared before Welcome Message job", [
                    'deleted_jobs' => $deletedCount
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to clear jobs table: " . $e->getMessage());
        }
    }
}
