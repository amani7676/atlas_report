<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RealTimeJobCleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:clean-realtime {--interval=30 : Check interval in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Real-time job cleaner that runs continuously';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interval = $this->option('interval');
        
        $this->info('🔄 Starting Real-Time Job Cleaner...');
        $this->info("⏰ Checking every {$interval} seconds");
        $this->info('🛑 Press Ctrl+C to stop');

        while (true) {
            try {
                $this->cleanJobs();
                
                // نمایش وضعیت
                $jobCount = DB::table('jobs')->count();
                $this->line("📊 Current jobs: {$jobCount} - " . now()->format('H:i:s'));
                
                // صبر برای چرخه بعدی
                sleep($interval);
                
            } catch (\Exception $e) {
                $this->error("❌ Error: " . $e->getMessage());
                Log::error('Real-time cleaner error: ' . $e->getMessage());
                sleep(5); // صبر کوتاه در صورت خطا
            }
        }
    }

    /**
     * Clean up old jobs immediately
     */
    private function cleanJobs()
    {
        // پاکسازی jobs قدیمی‌تر از 30 ثانیه
        $thirtySecondsAgo = now()->subSeconds(30)->timestamp;
        
        $deletedCount = DB::table('jobs')
            ->where('created_at', '<', $thirtySecondsAgo)
            ->delete();

        // پاکسازی job batches تمام شده
        DB::table('job_batches')
            ->where('pending_jobs', 0)
            ->where('finished_at', '<', now()->subMinutes(5)->timestamp)
            ->delete();

        // پاکسازی failed jobs قدیمی
        DB::table('failed_jobs')
            ->where('failed_at', '<', now()->subMinutes(10))
            ->delete();

        if ($deletedCount > 0) {
            $this->info("🗑️  Cleaned {$deletedCount} old jobs");
        }
    }
}
