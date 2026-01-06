<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use App\Jobs\SyncResidentsFromApi;

class PersistentAlarm extends Component
{
    public $showAlarm = true;
    public $syncing = false;

    /**
     * همگام‌سازی داده‌ها و رفرش صفحه
     */
    public function syncAndRefresh()
    {
        $this->syncing = true;
        
        try {
            // اجرای Job همگام‌سازی
            $job = new SyncResidentsFromApi();
            $job->handle();
            
            // نمایش پیام موفقیت کوتاه
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'همگام‌سازی موفق',
                'message' => 'دیتابیس با موفقیت به‌روزرسانی شد. صفحه در حال رفرش شدن است...',
                'duration' => 2000,
            ]);
            
            // رفرش صفحه بعد از 2 ثانیه
            $this->dispatch('refreshPage');
            
        } catch (\Exception $e) {
            \Log::error('Error syncing residents from PersistentAlarm component', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // نمایش پیام خطا
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا در همگام‌سازی',
                'message' => 'خطا در همگام‌سازی داده‌ها. لطفاً دوباره تلاش کنید.',
                'duration' => 3000,
            ]);
        } finally {
            $this->syncing = false;
        }
    }

    /**
     * بستن آلارم
     */
    public function closeAlarm()
    {
        $this->showAlarm = false;
    }

    public function render()
    {
        return view('livewire.layout.persistent-alarm');
    }
}
