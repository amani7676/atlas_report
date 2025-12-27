<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// همگام‌سازی خودکار اطلاعات اقامت‌گران از API هر ساعت
Schedule::command('residents:sync')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
