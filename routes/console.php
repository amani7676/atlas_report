<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// همگام‌سازی خودکار اطلاعات اقامت‌گران از API هر 1 دقیقه (غیرفعال شده)
// Schedule::command('residents:sync')
//     ->everyMinute()
//     ->withoutOverlapping()
//     ->runInBackground();
