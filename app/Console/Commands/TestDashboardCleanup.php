<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDashboardCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:dashboard-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test dashboard orphaned records cleanup with jobs table clearing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Dashboard Cleanup with Jobs Table Clearing...');
        $this->info('=======================================================');

        // شمارش وضعیت قبل از تست
        $beforeJobs = DB::table('jobs')->count();
        $beforeReports = \App\Models\ResidentReport::count();
        $beforeMessages = \App\Models\SmsMessageResident::count();
        
        $this->info("📊 Status before test:");
        $this->info("   Jobs: {$beforeJobs}");
        $this->info("   Reports: {$beforeReports}");
        $this->info("   Messages: {$beforeMessages}");

        // ایجاد چند job تستی برای شبیه‌سازی وضعیت واقعی
        if ($beforeJobs === 0) {
            $this->info('📤 Creating test jobs...');
            for ($i = 0; $i < 3; $i++) {
                \App\Jobs\TestAutoDelete::dispatch();
            }
            $beforeJobs = DB::table('jobs')->count();
            $this->info("📊 Created {$beforeJobs} test jobs");
        }

        $this->newLine();
        $this->info('🔄 Simulating dashboard cleanup button click...');

        try {
            // ایجاد نمونه از Dashboard و فراخوانی متد پاکسازی
            $dashboard = new \App\Livewire\Dashboard();
            $dashboard->cleanupOrphanedRecords();
            
            $this->info('✅ Dashboard cleanup executed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Dashboard cleanup failed: ' . $e->getMessage());
            return 1;
        }

        // بررسی وضعیت بعد از اجرا
        $afterJobs = DB::table('jobs')->count();
        $afterReports = \App\Models\ResidentReport::count();
        $afterMessages = \App\Models\SmsMessageResident::count();
        
        $this->newLine();
        $this->info("📊 Status after test:");
        $this->info("   Jobs: {$afterJobs}");
        $this->info("   Reports: {$afterReports}");
        $this->info("   Messages: {$afterMessages}");

        // محاسبه تعداد حذف شده
        $deletedJobs = $beforeJobs - $afterJobs;
        $deletedReports = $beforeReports - $afterReports;
        $deletedMessages = $beforeMessages - $afterMessages;

        // نتیجه
        $this->newLine();
        if ($afterJobs === 0) {
            $this->info('🎉 SUCCESS: Jobs table is clean after dashboard cleanup!');
            $this->info('✅ Dashboard cleanup system is working perfectly!');
        } else {
            $this->warn('⚠️  WARNING: Jobs table is not clean after dashboard cleanup.');
            $this->info("💡 {$afterJobs} jobs remain in the table.");
        }

        $this->info("📋 Summary of deleted items:");
        $this->info("   - Jobs deleted: {$deletedJobs}");
        $this->info("   - Reports deleted: {$deletedReports}");
        $this->info("   - Messages deleted: {$deletedMessages}");

        $this->newLine();
        $this->info('✅ Test completed!');
        return 0;
    }
}
