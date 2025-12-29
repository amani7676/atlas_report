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
                // ساخت پیام با پاسخ دیتابیس
                if ($lastSync) {
                    $message = "✅ همگام‌سازی با موفقیت انجام شد\n\n";
                    $message .= "📊 آمار همگام‌سازی:\n";
                    $message .= "• تعداد همگام‌سازی شده: {$lastSync['synced_count']}\n";
                    $message .= "• ایجاد شده: {$lastSync['created_count']}\n";
                    $message .= "• به‌روزرسانی شده: {$lastSync['updated_count']}\n\n";
                    $message .= "💾 پاسخ دیتابیس:\n";
                    $message .= "• تعداد کل در دیتابیس: {$totalInDb}\n";
                    $message .= "• آخرین همگام‌سازی: {$lastSyncTime}\n";
                    $message .= "• زمان همگام‌سازی: {$lastSync['time']}";
                } else {
                    $message = "✅ همگام‌سازی با موفقیت انجام شد\n\n";
                    $message .= "💾 پاسخ دیتابیس:\n";
                    $message .= "• تعداد کل در دیتابیس: {$totalInDb}\n";
                    $message .= "• آخرین همگام‌سازی: {$lastSyncTime}";
                }
                
                // نمایش آلارم در بالا سمت چپ با پاسخ دیتابیس
                $this->dispatch('showToast', [
                    'type' => 'success',
                    'title' => 'بروزرسانی شد',
                    'message' => $message,
                    'duration' => 8000, // 8 ثانیه برای خواندن اطلاعات بیشتر
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
                    'title' => 'خطا!',
                    'message' => 'خطا در همگام‌سازی داده‌ها: ' . $e->getMessage(),
                    'duration' => 5000,
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

