<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// همگام‌سازی خودکار اطلاعات اقامت‌گران از API
// این Schedule هر دقیقه چک می‌کند که آیا زمان sync رسیده است یا نه
Schedule::call(function () {
    $settings = \App\Models\Settings::getSettings();
    $refreshInterval = $settings->refresh_interval ?? 5;
    
    if ($refreshInterval <= 0) {
        return; // رفرش غیرفعال است
    }
    
    // بررسی زمان آخرین sync
    $lastSyncTime = \Illuminate\Support\Facades\Cache::get('residents_last_sync_time');
    $now = now();
    
    // اگر اولین بار است یا زمان sync رسیده است
    if (!$lastSyncTime || $now->diffInMinutes($lastSyncTime) >= $refreshInterval) {
        \Artisan::call('residents:sync');
        \Illuminate\Support\Facades\Cache::put('residents_last_sync_time', $now, now()->addDays(1));
        \Log::info('Residents sync executed via scheduler', [
            'refresh_interval' => $refreshInterval,
            'last_sync_time' => $lastSyncTime?->format('Y-m-d H:i:s'),
        ]);
    }
})->name('residents-auto-sync')
  ->everyMinute()
  ->withoutOverlapping();

// بررسی و ارسال پیامک‌های خودکار هر 5 دقیقه
Schedule::command('auto-sms:check')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// بررسی و ارسال پیام‌های خوش‌آمدگویی بر اساس تنظیمات
Schedule::call(function () {
    $settings = \App\Models\Settings::first();
    
    if (!$settings || !$settings->welcome_system_active) {
        return;
    }
    
    $interval = $settings->welcome_check_interval_minutes ?? 1;
    
    // بررسی اینکه آیا زمان اجرا رسیده است
    $lastRun = \Illuminate\Support\Facades\Cache::get('welcome_messages_last_run');
    $now = now();
    
    if (!$lastRun || $now->diffInMinutes($lastRun) >= $interval) {
        \Artisan::call('welcome:process');
        \Illuminate\Support\Facades\Cache::put('welcome_messages_last_run', $now, now()->addDays(1));
        \Log::info('Welcome messages processed via scheduler', [
            'interval' => $interval,
            'last_run' => $lastRun?->format('Y-m-d H:i:s'),
        ]);
    }
})->name('welcome-messages-auto-process')
  ->everyMinute()
  ->withoutOverlapping();
