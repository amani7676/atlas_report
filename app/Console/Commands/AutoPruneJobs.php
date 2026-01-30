<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoPruneJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:prune {--force : Force prune without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically prune completed jobs table after processing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting jobs table cleanup...');

        // شمارش وظایف قبل از پاکسازی
        $beforeCount = DB::table('jobs')->count();
        $this->info("📊 Jobs before cleanup: {$beforeCount}");

        if ($beforeCount === 0) {
            $this->info('✅ No jobs to clean up!');
            return 0;
        }

        // اگر force بود، همه را پاک کن
        if ($this->option('force')) {
            $this->warn('🔥 Force clearing all jobs...');
            $deletedCount = DB::table('jobs')->delete();
            $this->info("🗑️  Force deleted {$deletedCount} jobs");
        } else {
            // پاکسازی عادی (قدیمی‌ها)
            $this->pruneOldJobs();
        }

        // پاکسازی جداول دیگر
        $this->cleanupOtherTables();

        // شمارش بعد از پاکسازی
        $afterCount = DB::table('jobs')->count();
        $this->info("📊 Jobs after cleanup: {$afterCount}");

        // نمایش آمار
        $totalDeleted = $beforeCount - $afterCount;
        $this->info("🎉 Total jobs cleaned: {$totalDeleted}");

        // نمایش حجم جدول
        $tableSize = $this->getTableSize();
        $this->info("💾 Current table size: {$tableSize}");

        $this->info('✅ Jobs table cleanup completed successfully!');
        return 0;
    }

    /**
     * Prune old jobs (normal cleanup)
     */
    private function pruneOldJobs()
    {
        try {
            // حذف وظایف قدیمی‌تر از 5 دقیقه (timestamp format)
            $fiveMinutesAgo = now()->subMinutes(5)->timestamp;
            $deletedCount = DB::table('jobs')
                ->where('created_at', '<', $fiveMinutesAgo)
                ->delete();

            $this->info("🗑️  Deleted {$deletedCount} old jobs");

        } catch (\Exception $e) {
            $this->error("❌ Error during cleanup: " . $e->getMessage());
            Log::error('Jobs cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * Clean up other tables
     */
    private function cleanupOtherTables()
    {
        try {
            // پاکسازی job batches خالی
            $oneHourAgo = now()->subHours(1)->timestamp;
            $deletedBatches = DB::table('job_batches')
                ->where('pending_jobs', 0)
                ->where('finished_at', '<', $oneHourAgo)
                ->delete();

            // پاکسازی failed jobs قدیمی
            $deletedFailed = DB::table('failed_jobs')
                ->where('failed_at', '<', now()->subHours(1)->subMinutes(30))
                ->delete();

            $this->info("🗑️  Deleted {$deletedBatches} completed batches");
            $this->info("🗑️  Deleted {$deletedFailed} failed jobs");

        } catch (\Exception $e) {
            $this->error("❌ Error cleaning other tables: " . $e->getMessage());
            Log::error('Other tables cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * Get approximate table size
     */
    private function getTableSize()
    {
        try {
            $result = DB::select("SELECT 
                COUNT(*) as row_count,
                ROUND(SUM(LENGTH(payload)) / 1024, 2) as payload_kb
                FROM jobs");
            
            if (!empty($result)) {
                $rows = $result[0]->row_count;
                $kb = $result[0]->payload_kb;
                return "{$rows} rows (~{$kb} KB)";
            }
        } catch (\Exception $e) {
            // اگر کوئری اجرا نشد، فقط تعداد ردیف‌ها را برگردان
            $count = DB::table('jobs')->count();
            return "{$count} rows";
        }

        return 'Unknown';
    }
}
