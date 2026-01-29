<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use App\Jobs\SyncResidentsFromApi;
use Illuminate\Support\Facades\Log;

class PersistentAlarm extends Component
{
    public $showAlarm = true;
    public $syncing = false;

    public function syncAndRefresh()
    {
        $this->syncing = true;
        
        try {
            // اجرای Job همگام‌سازی
            $job = new SyncResidentsFromApi();
            $job->handle();
            
            // نمایش پیام موفقیت
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'همگام‌سازی موفق',
                'message' => 'دیتابیس با موفقیت به‌روزرسانی شد.',
                'duration' => 3000,
            ]);
            
            // مخفی کردن آلارم
            $this->showAlarm = false;
            
        } catch (\Exception $e) {
            Log::error('Error syncing residents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
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

    public function closeAlarm()
    {
        $this->showAlarm = false;
    }

    public function render()
    {
        return view('livewire.layout.persistent-alarm');
    }
}
