<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Resident;

class TestResetDataSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:reset-data-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test reset data and renumbering functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Reset Data and Renumbering...');
        $this->info('=====================================');

        // شمارش اقامت‌گران قبل از تست
        $beforeCount = Resident::count();
        $this->info("📊 Residents before test: {$beforeCount}");

        if ($beforeCount === 0) {
            $this->info('ℹ️  No residents found. Creating test data...');
            
            // ایجاد چند اقامت‌گر تستی
            for ($i = 1; $i <= 5; $i++) {
                Resident::create([
                    'resident_id' => 1000 + $i,
                    'resident_full_name' => "Test Resident {$i}",
                    'resident_phone' => "0912000000{$i}",
                    'resident_age' => 25 + $i,
                ]);
            }
            
            $beforeCount = Resident::count();
            $this->info("📊 Created {$beforeCount} test residents");
        }

        // نمایش شماره‌های فعلی
        $this->info('🔢 Current resident IDs:');
        $currentIds = Resident::orderBy('id')->pluck('id')->toArray();
        $this->info('   ' . implode(', ', $currentIds));

        $this->newLine();
        $this->info('🔄 Testing reset data sync...');

        try {
            // تست همگام‌سازی با بازنشانی داده‌ها
            $this->info('   1. Creating SyncResidentsFromApi job with resetData = true');
            
            $job = new \App\Jobs\SyncResidentsFromApi();
            $job->resetData = true;
            $job->handle();
            
            $this->info('✅ Job executed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Job execution failed: ' . $e->getMessage());
            return 1;
        }

        // بررسی نتایج
        $afterCount = Resident::count();
        $this->info("📊 Residents after reset: {$afterCount}");

        // نمایش شماره‌های جدید
        $this->info('🔢 New resident IDs:');
        $newIds = Resident::orderBy('id')->pluck('id')->toArray();
        $this->info('   ' . implode(', ', $newIds));

        // نتیجه
        $this->newLine();
        if ($newIds[0] === 1) {
            $this->info('🎉 SUCCESS: Resident IDs reset to start from 1!');
            $this->info('✅ Reset data system is working perfectly!');
        } else {
            $this->warn('⚠️  WARNING: Resident IDs were not reset properly.');
            $this->info("💡 First ID is: {$newIds[0]} (expected: 1)");
        }

        $this->newLine();
        $this->info('✅ Test completed!');
        return 0;
    }
}
