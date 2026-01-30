<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForceJobCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:force-cleanup {--all : Clean all jobs including recent ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force cleanup jobs table immediately';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔥 Force Cleaning Jobs Table...');
        
        $beforeCount = DB::table('jobs')->count();
        $this->info("📊 Jobs before cleanup: {$beforeCount}");

        if ($beforeCount === 0) {
            $this->info('✅ No jobs to clean!');
            return 0;
        }

        // حذف کامل جدول jobs
        if ($this->option('all')) {
            $this->warn('🗑️  Deleting ALL jobs...');
            $deletedCount = DB::table('jobs')->delete();
        } else {
            // حذف jobs قدیمی‌تر از 1 دقیقه
            $oneMinuteAgo = now()->subMinute()->timestamp;
            $deletedCount = DB::table('jobs')
                ->where('created_at', '<', $oneMinuteAgo)
                ->delete();
        }

        // پاکسازی جداول مرتبط
        DB::table('job_batches')->delete();
        DB::table('failed_jobs')->delete();

        $afterCount = DB::table('jobs')->count();
        $totalDeleted = $beforeCount - $afterCount;

        $this->info("🗑️  Deleted {$totalDeleted} jobs");
        $this->info("📊 Jobs after cleanup: {$afterCount}");
        $this->info('✅ Force cleanup completed!');

        return 0;
    }
}
