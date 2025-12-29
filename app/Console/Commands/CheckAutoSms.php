<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CheckAndSendAutoSms;

class CheckAutoSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-sms:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'بررسی و ارسال پیامک‌های خودکار';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('شروع بررسی پیامک‌های خودکار...');
        
        $job = new CheckAndSendAutoSms();
        $job->handle();
        
        $this->info('بررسی پیامک‌های خودکار با موفقیت انجام شد.');
        
        return 0;
    }
}
