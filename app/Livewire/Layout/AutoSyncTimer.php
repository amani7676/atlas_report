<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use App\Models\Settings;
use App\Jobs\SyncResidentsFromApi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AutoSyncTimer extends Component
{
    public $refreshInterval = 0; // فاصله رفرش به دقیقه
    public $isSyncing = false;
    public $initialTimeRemaining = 0; // زمان باقی‌مانده اولیه برای JavaScript

    protected $listeners = [
        'settings-updated' => 'reloadSettings',
    ];

    public function mount()
    {
        $this->loadSettings();
        $this->calculateInitialTime();
    }

    /**
     * بارگذاری تنظیمات از دیتابیس
     */
    public function loadSettings()
    {
        try {
            $settings = Settings::getSettings();
            $this->refreshInterval = $settings->refresh_interval ?? 0;
        } catch (\Exception $e) {
            Log::error('Error loading settings in AutoSyncTimer', [
                'error' => $e->getMessage(),
            ]);
            $this->refreshInterval = 0;
        }
    }

    /**
     * بارگذاری مجدد تنظیمات
     */
    public function reloadSettings()
    {
        $this->loadSettings();
        $this->calculateInitialTime();
    }

    /**
     * محاسبه زمان باقی‌مانده اولیه از Cache
     */
    public function calculateInitialTime()
    {
        if ($this->refreshInterval <= 0) {
            $this->initialTimeRemaining = 0;
            return;
        }

        // بررسی زمان آخرین sync از Cache
        $lastSyncTime = Cache::get('auto_sync_timer_start');
        $now = now();

        if ($lastSyncTime) {
            // محاسبه زمان باقی‌مانده
            $elapsedSeconds = $now->diffInSeconds($lastSyncTime);
            $totalSeconds = $this->refreshInterval * 60;
            $remaining = max(0, $totalSeconds - $elapsedSeconds);
            
            // تبدیل به عدد صحیح (ثانیه) - استفاده از floor برای اطمینان
            $this->initialTimeRemaining = (int) floor($remaining);
        } else {
            // شروع تایمر از اول
            $this->initialTimeRemaining = (int) ($this->refreshInterval * 60);
            Cache::put('auto_sync_timer_start', $now, now()->addDays(1));
        }
    }

    /**
     * دریافت زمان باقی‌مانده فعلی (برای JavaScript)
     */
    public function getTimeRemaining()
    {
        if ($this->refreshInterval <= 0) {
            return 0;
        }

        $lastSyncTime = Cache::get('auto_sync_timer_start');
        if (!$lastSyncTime) {
            return $this->refreshInterval * 60;
        }

        $now = now();
        $elapsedSeconds = $now->diffInSeconds($lastSyncTime);
        $totalSeconds = $this->refreshInterval * 60;
        $remaining = max(0, $totalSeconds - $elapsedSeconds);

        // تبدیل به عدد صحیح (ثانیه)
        return (int) $remaining;
    }

    /**
     * انجام همگام‌سازی
     */
    public function performSync()
    {
        if ($this->isSyncing) {
            return; // جلوگیری از sync همزمان
        }

        $this->isSyncing = true;
        
        try {
            Log::info('Auto sync triggered by timer', [
                'refresh_interval' => $this->refreshInterval,
            ]);

            // اجرای Job همگام‌سازی
            $job = new SyncResidentsFromApi();
            $job->handle();

            // به‌روزرسانی زمان شروع تایمر در Cache
            Cache::put('auto_sync_timer_start', now(), now()->addDays(1));

            // dispatch event برای به‌روزرسانی داده‌ها در صفحات
            $this->dispatch('data-synced');
            $this->dispatch('residents-synced'); // برای سازگاری با کامپوننت‌های موجود

            Log::info('Auto sync completed successfully');
        } catch (\Exception $e) {
            Log::error('Error in auto sync', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            $this->isSyncing = false;
            
            // محاسبه مجدد زمان باقی‌مانده
            $this->calculateInitialTime();
        }
    }

    public function render()
    {
        // اگر refresh interval غیرفعال است، کامپوننت را نمایش نده
        if ($this->refreshInterval <= 0) {
            return view('livewire.layout.auto-sync-timer', [
                'showTimer' => false,
            ]);
        }

        return view('livewire.layout.auto-sync-timer', [
            'showTimer' => true,
        ]);
    }
}
