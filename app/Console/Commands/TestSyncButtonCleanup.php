<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestSyncButtonCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:sync-button-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sync button jobs cleanup functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Sync Button Jobs Cleanup...');
        $this->info('=====================================');

        // ایجاد چند job تستی برای شبیه‌سازی وضعیت واقعی
        $this->info('📤 Creating test jobs...');
        
        for ($i = 0; $i < 5; $i++) {
            \App\Jobs\TestAutoDelete::dispatch();
        }
        
        $beforeCount = DB::table('jobs')->count();
        $this->info("📊 Jobs created: {$beforeCount}");

        if ($beforeCount === 0) {
            $this->warn('⚠️  No jobs were created. Skipping test.');
            return 0;
        }

        // شبیه‌سازی کلیک دکمه همگام‌سازی
        $this->info('🔄 Simulating sync button click...');
        
        try {
            // ایجاد نمونه از SyncButton و فراخوانی متد
            $syncButton = new \App\Livewire\Layout\SyncButton();
            $syncButton->syncResidents(false); // false = no toast
            
            $this->info('✅ Sync button executed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Sync button failed: ' . $e->getMessage());
            return 1;
        }

        // بررسی وضعیت بعد از اجرا
        $afterCount = DB::table('jobs')->count();
        $this->info("📊 Jobs after sync: {$afterCount}");

        // نتیجه
        if ($afterCount === 0) {
            $this->info('🎉 SUCCESS: Jobs table is clean after sync button click!');
            $this->info('✅ Sync button cleanup system is working perfectly!');
        } else {
            $this->warn('⚠️  WARNING: Jobs table is not clean after sync button.');
            $this->info("💡 {$afterCount} jobs remain in the table.");
        }

        $this->newLine();
        $this->info('✅ Test completed!');
        return 0;
    }
}
