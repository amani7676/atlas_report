<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CheckAndSendAutoSms;
use App\Jobs\ProcessAutoSmsOnDatabaseChange;

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
        
        // اجرای Job جدید برای پردازش تغییرات دیتابیس
        $job = new ProcessAutoSmsOnDatabaseChange();
        $job->handle();
        
        // اجرای Job قدیمی برای سازگاری
        $oldJob = new CheckAndSendAutoSms();
        $oldJob->handle();
        
        $this->info('بررسی پیامک‌های خودکار با موفقیت انجام شد.');
        
        return 0;
    }
}
