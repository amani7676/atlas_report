<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SmsMessageResident;
use App\Models\ResidentReport;
use App\Services\ResidentApiService;

class SyncResidentsFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'residents:sync {--force : Force sync without cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'همگام‌سازی اطلاعات اقامت‌گران از API با دیتابیس';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('شروع همگام‌سازی اطلاعات اقامت‌گران از API...');
        
        try {
            // اجرای Job برای sync کردن از API
            $job = new \App\Jobs\SyncResidentsFromApi();
            $job->handle();
            
            // نمایش آمار
            $lastSync = \Illuminate\Support\Facades\Cache::get('residents_last_sync');
            if ($lastSync) {
                $this->info('همگام‌سازی از API با موفقیت انجام شد.');
                $this->info("تعداد همگام‌سازی شده: {$lastSync['synced_count']}");
                $this->info("ایجاد شده: {$lastSync['created_count']}");
                $this->info("به‌روزرسانی شده: {$lastSync['updated_count']}");
                
                // بررسی تعداد واقعی در دیتابیس
                $actualCount = \App\Models\Resident::count();
                $this->info("تعداد واقعی در دیتابیس: {$actualCount}");
            } else {
                $this->warn('اطلاعات همگام‌سازی در cache یافت نشد.');
                $actualCount = \App\Models\Resident::count();
                $this->info("تعداد واقعی در دیتابیس: {$actualCount}");
            }
            
            // همگام‌سازی SmsMessageResident با جدول residents
            $this->newLine();
            $this->info('همگام‌سازی پیام‌های SMS با جدول residents...');
            $apiService = new ResidentApiService();
            $smsCount = SmsMessageResident::whereNotNull('resident_id')->count();
            $bar = $this->output->createProgressBar($smsCount);
            $bar->start();

            SmsMessageResident::whereNotNull('resident_id')->chunk(100, function ($messages) use ($apiService, $bar) {
                foreach ($messages as $message) {
                    // پیدا کردن resident در جدول residents
                    $resident = \App\Models\Resident::where('resident_id', $message->resident_id)->first();
                    if ($resident) {
                        $message->update(['resident_id' => $resident->id]);
                    }
                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();

            // همگام‌سازی ResidentReport با جدول residents
            $this->info('همگام‌سازی گزارش‌های اقامت‌گران با جدول residents...');
            $reportCount = ResidentReport::whereNotNull('resident_id')->count();
            $bar = $this->output->createProgressBar($reportCount);
            $bar->start();

            ResidentReport::whereNotNull('resident_id')->chunk(100, function ($reports) use ($apiService, $bar) {
                foreach ($reports as $report) {
                    // پیدا کردن resident در جدول residents
                    $resident = \App\Models\Resident::where('resident_id', $report->resident_id)->first();
                    if ($resident) {
                        $report->update(['resident_id' => $resident->id]);
                    }
                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();

            $this->info('همگام‌سازی با موفقیت انجام شد!');
            
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
