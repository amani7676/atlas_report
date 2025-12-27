<?php

namespace App\Listeners;

use App\Events\ResidentReportCreated;
use App\Models\SmsMessage;
use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendViolationSms implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ResidentReportCreated $event): void
    {
        // TODO: پیاده‌سازی ارسال خودکار SMS برای تخلفات
        // این بخش بعداً پیاده‌سازی خواهد شد
    }
}
