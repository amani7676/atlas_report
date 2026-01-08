<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Report;
use App\Models\Category;
use App\Models\SmsMessageResident;

class Dashboard extends Component
{
    public $totalReports;
    public $totalCategories;
    public $recentReports;
    public $recentSentMessages;
    public $totalSentMessages;
    public $failedMessages;

    public function mount()
    {
        $this->totalReports = Report::count();
        $this->totalCategories = Category::count();
        $this->recentReports = Report::with('category')
            ->latest()
            ->take(5)
            ->get();
        
        // دریافت پیام‌های ارسال شده اخیر
        $this->recentSentMessages = SmsMessageResident::with(['smsMessage'])
            ->latest()
            ->take(5)
            ->get();
        
        // آمار پیام‌ها
        $this->totalSentMessages = SmsMessageResident::count();
        $this->failedMessages = SmsMessageResident::where('status', 'failed')->count();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
