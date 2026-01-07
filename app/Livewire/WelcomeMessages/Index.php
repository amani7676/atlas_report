<?php

namespace App\Livewire\WelcomeMessages;

use Livewire\Component;
use App\Models\Report;
use App\Models\Settings;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // فقط 3 تنظیم اصلی
    public $selected_report_id = null;
    public $welcome_start_date = null;
    public $welcome_check_interval_minutes = 1;
    public $welcome_system_active = false;

    protected $rules = [
        'selected_report_id' => 'required|exists:reports,id',
        'welcome_start_date' => 'required|date',
        'welcome_check_interval_minutes' => 'required|integer|min:1',
        'welcome_system_active' => 'boolean',
    ];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $settings = Settings::first();
        if ($settings) {
            $this->selected_report_id = $settings->welcome_report_id;
            $this->welcome_start_date = $settings->welcome_start_date;
            $this->welcome_check_interval_minutes = $settings->welcome_check_interval_minutes ?? 1;
            $this->welcome_system_active = $settings->welcome_system_active ?? false;
        }
    }

    public function saveSettings()
    {
        $this->validate();

        $settings = Settings::first();
        if (!$settings) {
            $settings = new Settings();
        }

        $settings->welcome_report_id = $this->selected_report_id;
        $settings->welcome_start_date = $this->welcome_start_date;
        $settings->welcome_check_interval_minutes = $this->welcome_check_interval_minutes;
        $settings->welcome_system_active = $this->welcome_system_active;
        $settings->save();

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت',
            'message' => 'تنظیمات با موفقیت ذخیره شد.',
        ]);
    }

    public function render()
    {
        $reports = Report::all();

        return view('livewire.welcome-messages.index', compact('reports'));
    }
}
