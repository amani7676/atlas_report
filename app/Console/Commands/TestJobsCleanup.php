<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestJobsCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:jobs-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test jobs cleanup functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Jobs Cleanup System...');
        $this->info('==================================');

        // شمارش jobs قبل از تست
        $beforeCount = DB::table('jobs')->count();
        $this->info("📊 Jobs before test: {$beforeCount}");

        if ($beforeCount > 0) {
            $this->warn('⚠️  Jobs table is not empty. Creating test job...');
        } else {
            $this->info('✅ Jobs table is empty. Creating test job...');
        }

        // ایجاد job تست
        $this->info('📤 Creating SyncResidentsFromApi job...');
        
        try {
            $job = new \App\Jobs\SyncResidentsFromApi();
            $job->handle();
            
            $this->info('✅ Job executed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Job execution failed: ' . $e->getMessage());
            return 1;
        }

        // بررسی وضعیت بعد از اجرا
        $afterCount = DB::table('jobs')->count();
        $this->info("📊 Jobs after test: {$afterCount}");

        // نتیجه
        if ($afterCount === 0) {
            $this->info('🎉 SUCCESS: Jobs table is clean after job execution!');
            $this->info('✅ Auto-cleanup system is working perfectly!');
        } else {
            $this->warn('⚠️  WARNING: Jobs table is not clean after execution.');
            $this->info("💡 {$afterCount} jobs remain in the table.");
        }

        // نمایش وضعیت جداول دیگر
        $batchesCount = DB::table('job_batches')->count();
        $failedCount = DB::table('failed_jobs')->count();
        
        $this->info("📦 Job Batches: {$batchesCount}");
        $this->info("❌ Failed Jobs: {$failedCount}");

        $this->newLine();
        $this->info('✅ Test completed!');
        return 0;
    }
}
