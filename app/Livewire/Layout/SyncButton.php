<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use App\Jobs\SyncResidentsFromApi;

class SyncButton extends Component
{
    public $syncing = false;
    public $syncMessage = '';

    /**
     * همگام‌سازی دستی داده‌های اقامت‌گران از API
     */
    public function syncResidents($showToast = true)
    {
        $this->syncing = true;
        $this->syncMessage = 'در حال همگام‌سازی...';
        
        try {
            // اجرای Job همگام‌سازی
            $job = new SyncResidentsFromApi();
            $job->handle();
            
            // دریافت آمار همگام‌سازی
            $lastSync = \Illuminate\Support\Facades\Cache::get('residents_last_sync');
            
            // بررسی تعداد واقعی در دیتابیس
            $totalInDb = \App\Models\Resident::count();
            $lastSyncedResident = \App\Models\Resident::orderBy('last_synced_at', 'desc')->first();
            $lastSyncTime = $lastSyncedResident && $lastSyncedResident->last_synced_at 
                ? $lastSyncedResident->last_synced_at->format('Y-m-d H:i:s') 
                : 'نامشخص';
            
            // نمایش آلارم فقط اگر showToast = true باشد (برای همگام‌سازی دستی)
            if ($showToast) {
                // نمایش آلارم ساده
                $this->dispatch('showToast', [
                    'type' => 'success',
                    'title' => 'Success',
                    'message' => '',
                    'duration' => 3000,
                ]);
            }
            
            // پاک کردن پیام همگام‌سازی از صفحه
            $this->syncMessage = '';
        } catch (\Exception $e) {
            \Log::error('Error syncing residents from SyncButton component', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // نمایش آلارم خطا فقط اگر showToast = true باشد
            if ($showToast) {
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => '',
                    'duration' => 3000,
                ]);
            }
            
            // پاک کردن پیام همگام‌سازی از صفحه
            $this->syncMessage = '';
        } finally {
            $this->syncing = false;
        }
    }

    public function render()
    {
        return view('livewire.layout.sync-button');
    }
}

