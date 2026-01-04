<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Settings;
use App\Models\Pattern;
use App\Models\SmsMessageResident;
use App\Models\Resident;
use App\Jobs\SendWelcomeMessages;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Welcome extends Component
{
    use WithPagination;

    // Settings
    public $welcome_pattern_id = null;
    public $welcome_start_datetime = '';
    public $welcome_start_date = '';
    public $welcome_start_time = '';

    // Filters
    public $search_name = '';
    public $search_phone = '';
    public $search_unit = '';
    public $search_room = '';
    public $search_bed = '';

    // Lists for filters
    public $unitsList = [];
    public $roomsList = [];
    public $bedsList = [];

    /**
     * Listener برای event residents-synced
     * وقتی داده‌ها از API sync می‌شوند، این متد فراخوانی می‌شود
     */
    protected $listeners = ['residents-synced' => 'refreshData'];
    
    /**
     * Refresh کردن داده‌ها بعد از sync
     */
    public function refreshData()
    {
        $this->resetPage();
        $this->loadFilterOptions();
    }

    public function mount()
    {
        $settings = Settings::getSettings();
        $this->welcome_pattern_id = $settings->welcome_pattern_id;
        
        if ($settings->welcome_start_datetime) {
            $datetime = Carbon::parse($settings->welcome_start_datetime);
            $this->welcome_start_date = $datetime->format('Y-m-d');
            $this->welcome_start_time = $datetime->format('H:i:s');
        } else {
            $this->welcome_start_date = now()->format('Y-m-d');
            $this->welcome_start_time = now()->format('H:i:s');
        }

        $this->loadFilterOptions();
    }

    public function loadFilterOptions()
    {
        // بارگذاری لیست واحدها
        $this->unitsList = Resident::whereNotNull('unit_name')
            ->distinct()
            ->orderBy('unit_name', 'asc')
            ->pluck('unit_name')
            ->toArray();

        // بارگذاری لیست اتاق‌ها
        $this->roomsList = Resident::whereNotNull('room_name')
            ->distinct()
            ->orderBy('room_name', 'asc')
            ->pluck('room_name')
            ->toArray();

        // بارگذاری لیست تخت‌ها
        $this->bedsList = Resident::whereNotNull('bed_name')
            ->distinct()
            ->orderBy('bed_name', 'asc')
            ->pluck('bed_name')
            ->toArray();
    }

    public function saveSettings()
    {
        $this->validate([
            'welcome_pattern_id' => 'required|exists:patterns,id',
            'welcome_start_date' => 'required|date',
            'welcome_start_time' => 'required',
        ], [
            'welcome_pattern_id.required' => 'انتخاب الگوی پیام خوش‌آمدگویی الزامی است.',
            'welcome_pattern_id.exists' => 'الگوی انتخاب شده معتبر نیست.',
            'welcome_start_date.required' => 'تاریخ شروع الزامی است.',
            'welcome_start_date.date' => 'تاریخ شروع باید معتبر باشد.',
            'welcome_start_time.required' => 'زمان شروع الزامی است.',
        ]);

        try {
            $datetime = Carbon::parse($this->welcome_start_date . ' ' . $this->welcome_start_time);
            
            Settings::updateSettings([
                'welcome_pattern_id' => $this->welcome_pattern_id,
                'welcome_start_datetime' => $datetime,
            ]);

            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'message' => 'تنظیمات خوش‌آمدگویی با موفقیت ذخیره شد.',
                'duration' => 3000,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا!',
                'message' => 'خطا در ذخیره تنظیمات: ' . $e->getMessage(),
                'duration' => 5000,
            ]);
        }
    }

    public function sendWelcomeMessages()
    {
        try {
            SendWelcomeMessages::dispatch();
            
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'message' => 'Job ارسال پیام‌های خوش‌آمدگویی در صف قرار گرفت.',
                'duration' => 3000,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا!',
                'message' => 'خطا در ارسال Job: ' . $e->getMessage(),
                'duration' => 5000,
            ]);
        }
    }

    public function updatedSearchName()
    {
        $this->resetPage();
    }

    public function updatedSearchPhone()
    {
        $this->resetPage();
    }

    public function updatedSearchUnit()
    {
        $this->resetPage();
    }

    public function updatedSearchRoom()
    {
        $this->resetPage();
    }

    public function updatedSearchBed()
    {
        $this->resetPage();
    }

    public function render()
    {
        $settings = Settings::getSettings();
        $pattern = $settings->welcome_pattern_id ? Pattern::find($settings->welcome_pattern_id) : null;

        // Query برای پیام‌های خوش‌آمدگویی
        $query = SmsMessageResident::query()
            ->with('resident')
            ->orderBy('created_at', 'desc');

        // فقط اگر الگو تنظیم شده باشد، فیلتر کن
        if ($settings->welcome_pattern_id) {
            $query->where('pattern_id', $settings->welcome_pattern_id);
        } else {
            // اگر الگو تنظیم نشده، هیچ پیامی نمایش نده
            $query->whereRaw('1 = 0');
        }

        // اعمال فیلترها
        if ($this->search_name) {
            $query->where('resident_name', 'like', '%' . $this->search_name . '%');
        }

        if ($this->search_phone) {
            $query->where('phone', 'like', '%' . $this->search_phone . '%');
        }

        if ($this->search_unit) {
            $query->whereHas('resident', function($q) {
                $q->where('unit_name', 'like', '%' . $this->search_unit . '%');
            });
        }

        if ($this->search_room) {
            $query->whereHas('resident', function($q) {
                $q->where('room_name', 'like', '%' . $this->search_room . '%');
            });
        }

        if ($this->search_bed) {
            $query->whereHas('resident', function($q) {
                $q->where('bed_name', 'like', '%' . $this->search_bed . '%');
            });
        }

        $messages = $query->paginate(20);

        $patterns = Pattern::where('is_active', true)->orderBy('title', 'asc')->get();

        return view('livewire.residents.welcome', [
            'messages' => $messages,
            'patterns' => $patterns,
            'pattern' => $pattern,
            'settings' => $settings,
        ]);
    }
}
