<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncResidentsFromApi;
use Illuminate\Support\Facades\Log;

class SyncResidentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'residents:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'همگام‌سازی اطلاعات اقامت‌گران از API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('شروع همگام‌سازی اطلاعات اقامت‌گران...');
        
        try {
            // اجرای مستقیم Job بدون queue
            $job = new SyncResidentsFromApi();
            $job->handle();
            
            // نمایش آمار
            $lastSync = \Illuminate\Support\Facades\Cache::get('residents_last_sync');
            if ($lastSync) {
                $this->info('همگام‌سازی با موفقیت انجام شد.');
                $this->info("حذف شده: {$lastSync['deleted_count']}");
                $this->info("ایجاد شده: {$lastSync['created_count']}");
                $this->info("مجموع همگام‌سازی شده: {$lastSync['synced_count']}");
                
                // بررسی تعداد واقعی در دیتابیس
                $actualCount = \App\Models\Resident::count();
                $this->info("تعداد واقعی در دیتابیس: {$actualCount}");
            } else {
                $this->warn('اطلاعات همگام‌سازی در cache یافت نشد.');
                $actualCount = \App\Models\Resident::count();
                $this->info("تعداد واقعی در دیتابیس: {$actualCount}");
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('خطا در همگام‌سازی: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Sync residents command error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
