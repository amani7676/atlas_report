<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestJobsIdReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:jobs-id-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test jobs table ID reset functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Jobs Table ID Reset...');
        $this->info('==================================');

        // ایجاد چند job با ID های بالا برای تست
        $this->info('📤 Creating test jobs with high IDs...');
        
        // شبیه‌سازی job با ID های بالا
        for ($i = 0; $i < 5; $i++) {
            \App\Jobs\SyncResidentsFromApi::dispatch();
        }
        
        // بررسی ID های فعلی
        $currentIds = DB::table('jobs')->orderBy('id')->pluck('id')->toArray();
        $this->info("🔢 Current job IDs: " . implode(', ', $currentIds));

        if (empty($currentIds)) {
            $this->warn('⚠️  No jobs found. Creating test jobs first...');
            return 0;
        }

        $maxId = max($currentIds);
        $this->info("📊 Highest job ID: {$maxId}");

        $this->newLine();
        $this->info('🔄 Testing ID reset functionality...');

        try {
            // تست ریست کردن ID از طریق Dashboard
            $this->info('   1. Testing Dashboard cleanup with ID reset...');
            
            $dashboard = new \App\Livewire\Dashboard();
            $dashboard->cleanupOrphanedRecords();
            
            $this->info('✅ Dashboard cleanup executed!');
            
        } catch (\Exception $e) {
            $this->error('❌ Dashboard cleanup failed: ' . $e->getMessage());
            return 1;
        }

        // بررسی نتایج
        $this->newLine();
        $this->info('📊 Checking results...');
        
        // ایجاد چند job جدید برای تست ID های جدید
        $this->info('   2. Creating new jobs to test ID reset...');
        for ($i = 0; $i < 3; $i++) {
            \App\Jobs\SyncResidentsFromApi::dispatch();
        }
        
        $newIds = DB::table('jobs')->orderBy('id')->pluck('id')->toArray();
        $this->info("🔢 New job IDs after reset: " . implode(', ', $newIds));

        // نتیجه
        $this->newLine();
        if ($newIds[0] === 1) {
            $this->info('🎉 SUCCESS: Job IDs reset to start from 1!');
            $this->info('✅ ID reset system is working perfectly!');
            
            // نمایش اطلاعات دقیق
            $this->info("📋 Details:");
            $this->info("   - Previous highest ID: {$maxId}");
            $this->info("   - New IDs start from: {$newIds[0]}");
            $this->info("   - Total jobs now: " . count($newIds));
        } else {
            $this->warn('⚠️  WARNING: Job IDs were not reset properly.');
            $this->info("💡 First ID is: {$newIds[0]} (expected: 1)");
        }

        $this->newLine();
        $this->info('✅ Test completed!');
        return 0;
    }
}
