<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncAlert extends Component
{
    public $showAlert = false;
    public $alertType = 'success'; // success, error, warning
    public $alertMessage = '';
    public $alertTitle = '';
    public $lastSyncData = null;

    protected $listeners = [
        'residents-synced' => 'checkSyncStatus',
        'data-synced' => 'checkSyncStatus',
    ];

    public function mount()
    {
        $this->checkSyncStatus();
    }

    /**
     * بررسی وضعیت آخرین سینک
     */
    public function checkSyncStatus()
    {
        try {
            $lastSync = Cache::get('residents_last_sync');
            
            if ($lastSync) {
                $this->lastSyncData = $lastSync;
                $this->alertType = 'success';
                $this->alertTitle = 'همگام‌سازی موفق';
                $this->alertMessage = $lastSync['message'] ?? "دیتابیس به‌روزرسانی شد. تعداد: {$lastSync['synced_count']}";
                $this->showAlert = true;
                
                // بعد از 5 ثانیه آلارم را مخفی کن
                $this->dispatch('hide-alert-after-delay');
            } else {
                // بررسی خطاهای احتمالی
                $lastError = Cache::get('residents_sync_error');
                if ($lastError) {
                    $this->alertType = 'error';
                    $this->alertTitle = 'خطا در همگام‌سازی';
                    $this->alertMessage = $lastError['message'] ?? 'خطا در همگام‌سازی داده‌ها';
                    $this->showAlert = true;
                    
                    // بعد از 10 ثانیه آلارم خطا را مخفی کن
                    $this->dispatch('hide-alert-after-delay');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking sync status in SyncAlert', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * بستن آلارم
     */
    public function closeAlert()
    {
        $this->showAlert = false;
    }

    /**
     * Polling برای بررسی وضعیت سینک (هر 10 ثانیه)
     */
    public function pollSyncStatus()
    {
        // فقط اگر آلارم نمایش داده نشده باشد، بررسی کن
        // تا از نمایش مکرر آلارم جلوگیری شود
        if (!$this->showAlert) {
            $this->checkSyncStatus();
        }
    }

    public function render()
    {
        return view('livewire.layout.sync-alert');
    }
}

