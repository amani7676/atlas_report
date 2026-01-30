<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class JobsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:status {--detailed : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show current status of jobs table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Jobs Table Status');
        $this->info('==================');

        // آمار کلی
        $jobsCount = DB::table('jobs')->count();
        $batchesCount = DB::table('job_batches')->count();
        $failedCount = DB::table('failed_jobs')->count();

        $this->info("📋 Pending Jobs: {$jobsCount}");
        $this->info("📦 Job Batches: {$batchesCount}");
        $this->info("❌ Failed Jobs: {$failedCount}");

        if ($this->option('detailed')) {
            $this->newLine();
            $this->info('🔍 Detailed Information:');
            $this->info('========================');

            // اطلاعات jobs
            if ($jobsCount > 0) {
                $oldestJob = DB::table('jobs')->min('created_at');
                $newestJob = DB::table('jobs')->max('created_at');
                
                $this->info("⏰ Oldest Job: " . date('Y-m-d H:i:s', $oldestJob));
                $this->info("⏰ Newest Job: " . date('Y-m-d H:i:s', $newestJob));

                // تعداد بر اساس صف
                $queues = DB::table('jobs')
                    ->select('queue', DB::raw('count(*) as count'))
                    ->groupBy('queue')
                    ->get();

                $this->info('📤 Jobs by Queue:');
                foreach ($queues as $queue) {
                    $this->info("   - {$queue->queue}: {$queue->count}");
                }

                // محاسبه حجم
                $totalSize = DB::table('jobs')
                    ->selectRaw('SUM(LENGTH(payload)) as total_size')
                    ->value('total_size');
                
                $this->info("💾 Total Payload Size: " . round($totalSize / 1024, 2) . ' KB');
            }

            // اطلاعات failed jobs
            if ($failedCount > 0) {
                $this->newLine();
                $this->info('❌ Failed Jobs Details:');
                
                $recentFailures = DB::table('failed_jobs')
                    ->select('exception', 'failed_at')
                    ->orderBy('failed_at', 'desc')
                    ->limit(3)
                    ->get();

                foreach ($recentFailures as $failure) {
                    $this->info("   - " . substr($failure->exception, 0, 50) . "...");
                    $this->info("     Failed at: " . $failure->failed_at);
                }
            }
        }

        $this->newLine();
        
        // هشدارها
        if ($jobsCount > 100) {
            $this->warn('⚠️  Warning: High number of pending jobs!');
            $this->info('💡 Consider running: php artisan jobs:prune --force');
        }

        if ($failedCount > 10) {
            $this->warn('⚠️  Warning: Many failed jobs detected!');
            $this->info('💡 Consider checking: php artisan queue:failed');
        }

        $this->info('✅ Status check completed!');
    }
}
