<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use App\Models\Settings;
use Illuminate\Support\Facades\Log;

class AutoSync extends Component
{
    public $refreshInterval = 0;

    public function mount()
    {
        $this->loadSettings();
    }

    /**
     * بارگذاری تنظیمات از دیتابیس
     */
    public function loadSettings()
    {
        try {
            $settings = Settings::getSettings();
            $this->refreshInterval = (int) ($settings->refresh_interval ?? 0);
            
            Log::info('AutoSync: Settings loaded from database', [
                'refresh_interval' => $this->refreshInterval,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading settings in AutoSync', [
                'error' => $e->getMessage(),
            ]);
            $this->refreshInterval = 0;
        }
    }

    public function render()
    {
        return view('livewire.layout.auto-sync');
    }
}
