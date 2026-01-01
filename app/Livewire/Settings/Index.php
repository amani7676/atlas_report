<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Settings;

class Index extends Component
{
    public $refresh_interval = 5;
    public $api_url = '';

    protected function rules()
    {
        return [
            'refresh_interval' => 'required|integer|min:0|max:1440',
            'api_url' => 'required|url',
        ];
    }

    protected $messages = [
        'refresh_interval.required' => 'میزان رفرش صفحه الزامی است.',
        'refresh_interval.integer' => 'میزان رفرش باید عدد باشد.',
        'refresh_interval.min' => 'میزان رفرش نمی‌تواند منفی باشد. برای غیرفعال کردن رفرش خودکار، مقدار 0 را وارد کنید.',
        'refresh_interval.max' => 'میزان رفرش نمی‌تواند بیشتر از 1440 دقیقه (24 ساعت) باشد.',
        'api_url.required' => 'لینک API الزامی است.',
        'api_url.url' => 'لینک API باید یک URL معتبر باشد.',
    ];

    public function mount()
    {
        $settings = Settings::getSettings();
        $this->refresh_interval = $settings->refresh_interval ?? 5;
        $this->api_url = $settings->api_url ?? 'http://atlas2.test/api/residents';
    }

    public function save()
    {
        $this->validate();

        Settings::updateSettings([
            'refresh_interval' => $this->refresh_interval,
            'api_url' => $this->api_url,
        ]);

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => 'تنظیمات با موفقیت ذخیره شد.',
            'duration' => 3000,
        ]);

        // ارسال event برای به‌روزرسانی JavaScript
        $this->dispatch('settings-updated');
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
