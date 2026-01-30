<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCleanupJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:auto-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cleanup jobs table every hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('🔄 Starting automatic jobs cleanup...');

            // شمارش قبل از پاکسازی
            $beforeCount = DB::table('jobs')->count();
            
            if ($beforeCount > 50) {
                // اگر بیشتر از 50 تا بود، قدیمی‌ها را پاک کن
                $fiveMinutesAgo = now()->subMinutes(5)->timestamp;
                $deletedCount = DB::table('jobs')
                    ->where('created_at', '<', $fiveMinutesAgo)
                    ->delete();

                $this->info("🗑️  Auto-cleaned {$deletedCount} old jobs");
            } else {
                $this->info("✅ Only {$beforeCount} jobs found, no cleanup needed");
            }

            // پاکسازی جداول دیگر
            DB::table('job_batches')
                ->where('pending_jobs', 0)
                ->where('finished_at', '<', now()->subHours(2)->timestamp)
                ->delete();

            DB::table('failed_jobs')
                ->where('failed_at', '<', now()->subHours(6))
                ->delete();

            $this->info('✅ Automatic jobs cleanup completed!');

        } catch (\Exception $e) {
            $this->error('❌ Auto-cleanup failed: ' . $e->getMessage());
            Log::error('Auto jobs cleanup failed: ' . $e->getMessage());
        }
    }
}
