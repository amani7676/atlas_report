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
        $apiService = new ResidentApiService();
        
        if ($this->option('force')) {
            $apiService->clearCache();
        }

        $this->info('شروع همگام‌سازی اطلاعات اقامت‌گران...');

        // همگام‌سازی SmsMessageResident
        $this->info('همگام‌سازی پیام‌های SMS...');
        $smsCount = SmsMessageResident::whereNotNull('resident_id')->count();
        $bar = $this->output->createProgressBar($smsCount);
        $bar->start();

        SmsMessageResident::whereNotNull('resident_id')->chunk(100, function ($messages) use ($apiService, $bar) {
            foreach ($messages as $message) {
                $apiService->syncResidentData($message);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        // همگام‌سازی ResidentReport
        $this->info('همگام‌سازی گزارش‌های اقامت‌گران...');
        $reportCount = ResidentReport::whereNotNull('resident_id')->count();
        $bar = $this->output->createProgressBar($reportCount);
        $bar->start();

        ResidentReport::whereNotNull('resident_id')->chunk(100, function ($reports) use ($apiService, $bar) {
            foreach ($reports as $report) {
                $apiService->syncResidentData($report);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info('همگام‌سازی با موفقیت انجام شد!');
        
        return Command::SUCCESS;
    }
}
