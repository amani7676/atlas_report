<?php

namespace App\Providers;

use App\Events\DatabaseRecordChanged;
use App\Events\ResidentReportCreated;
use App\Listeners\ProcessAutoSmsOnChange;
use App\Listeners\SendViolationSms;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تنظیم timezone به Asia/Tehran
        date_default_timezone_set('Asia/Tehran');
        Carbon::setLocale('fa');

        Event::listen(
            ResidentReportCreated::class,
            SendViolationSms::class
        );

        Event::listen(
            DatabaseRecordChanged::class,
            ProcessAutoSmsOnChange::class
        );
    }
}
