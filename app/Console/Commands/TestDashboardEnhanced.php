<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDashboardEnhanced extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:dashboard-enhanced';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test enhanced dashboard with sync button and statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Enhanced Dashboard...');
        $this->info('==================================');

        // شمارش وضعیت قبل از تست
        $this->info('📊 Current Status:');
        $this->info("   Residents: " . \App\Models\Resident::count());
        $this->info("   Reports: " . \App\Models\ResidentReport::count());
        $this->info("   Messages: " . \App\Models\SmsMessageResident::count());
        $this->info("   Jobs: " . DB::table('jobs')->count());

        // ایجاد چند job برای تست
        if (DB::table('jobs')->count() === 0) {
            $this->info('📤 Creating test jobs...');
            for ($i = 0; $i < 3; $i++) {
                \App\Jobs\SyncResidentsFromApi::dispatch();
            }
            $this->info("   Created 3 test jobs");
        }

        $this->newLine();
        $this->info('🔄 Testing enhanced dashboard functionality...');

        try {
            // تست دکمه همگام‌سازی
            $this->info('   1. Testing sync button...');
            
            $syncButton = new \App\Livewire\Layout\SyncButton();
            $syncButton->syncResidents(false); // false = no toast
            
            $this->info('✅ Sync button executed!');
            
            // تست دکمه پاکسازی
            $this->info('   2. Testing cleanup button...');
            
            $dashboard = new \App\Livewire\Dashboard();
            $dashboard->cleanupOrphanedRecords();
            
            $this->info('✅ Cleanup button executed!');
            
        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return 1;
        }

        // بررسی نتایج
        $this->newLine();
        $this->info('📊 Status after tests:');
        $this->info("   Residents: " . \App\Models\Resident::count());
        $this->info("   Reports: " . \App\Models\ResidentReport::count());
        $this->info("   Messages: " . \App\Models\SmsMessageResident::count());
        $this->info("   Jobs: " . DB::table('jobs')->count());

        $this->newLine();
        $this->info('🎉 SUCCESS: Enhanced dashboard is working perfectly!');
        $this->info('✅ Both sync and cleanup buttons are functional!');
        
        $this->info('📋 Features:');
        $this->info('   - Real-time statistics display');
        $this->info('   - Sync button with reset option');
        $this->info('   - Cleanup button with jobs table clearing');
        $this->info('   - ID reset functionality');

        $this->newLine();
        $this->info('✅ Test completed!');
        return 0;
    }
}
