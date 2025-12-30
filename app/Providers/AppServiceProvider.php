<?php

namespace App\Providers;

use App\Events\DatabaseRecordChanged;
use App\Events\ResidentReportCreated;
use App\Listeners\ProcessAutoSmsOnChange;
use App\Listeners\SendViolationSms;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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
