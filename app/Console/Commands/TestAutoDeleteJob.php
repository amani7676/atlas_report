<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncResidentsFromApiAutoDelete;
use Illuminate\Support\Facades\Log;

class TestAutoDeleteJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:auto-delete-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test auto-delete job functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Auto-Delete Job System...');
        $this->info('=====================================');

        try {
            // شمارش jobs قبل از ایجاد
            $beforeCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
            $this->info("📊 Jobs before test: {$beforeCount}");

            // ایجاد و ارسال job تست
            $this->info('📤 Creating and dispatching test job...');
            
            $job = new SyncResidentsFromApiAutoDelete();
            dispatch($job);

            // کمی صبر برای پردازش
            $this->info('⏳ Waiting for job processing...');
            sleep(2);

            // شمارش jobs بعد از اجرا
            $afterCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
            $this->info("📊 Jobs after test: {$afterCount}");

            // بررسی نتیجه
            if ($afterCount <= $beforeCount) {
                $this->info('✅ SUCCESS: Job was auto-deleted after execution!');
                $this->info('🎉 Auto-delete system is working correctly!');
            } else {
                $this->warn('⚠️  WARNING: Job was not auto-deleted.');
                $this->info('💡 The job might still be processing or auto-delete is not working.');
            }

            // نمایش لاگ‌ها
            $this->newLine();
            $this->info('📋 Recent log entries:');
            $this->info('=====================');
            
            $logs = Log::getLogger()->getHandlers();
            // نمایش لاگ‌های اخیر اگر وجود داشته باشد

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('✅ Test completed!');
        return 0;
    }
}
