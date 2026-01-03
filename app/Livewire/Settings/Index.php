<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Settings;

class Index extends Component
{
    public $refresh_interval = 5;
    public $api_url = '';
    public $sms_delay_before_start = 2; // تاخیر قبل از شروع ارسال (ثانیه)
    public $sms_delay_between_messages = 200; // تاخیر بین هر پیامک (میلی‌ثانیه)
    public $repeat_violation = 3; // تعداد گزارش یکسان برای نمایش
    public $count_violation = 5; // تعداد گزارش برای نمایش اقامت‌گران برتر
    public $max_violation = 10; // مجموع نمرات منفی برای نمایش اقامت‌گران برتر

    protected function rules()
    {
        return [
            'refresh_interval' => 'required|integer|min:0|max:1440',
            'api_url' => 'required|url',
            'sms_delay_before_start' => 'required|integer|min:0|max:60',
            'sms_delay_between_messages' => 'required|integer|min:0|max:5000',
            'repeat_violation' => 'required|integer|min:1',
            'count_violation' => 'required|integer|min:1',
            'max_violation' => 'required|integer|min:1',
        ];
    }

    protected $messages = [
        'refresh_interval.required' => 'میزان رفرش صفحه الزامی است.',
        'refresh_interval.integer' => 'میزان رفرش باید عدد باشد.',
        'refresh_interval.min' => 'میزان رفرش نمی‌تواند منفی باشد. برای غیرفعال کردن رفرش خودکار، مقدار 0 را وارد کنید.',
        'refresh_interval.max' => 'میزان رفرش نمی‌تواند بیشتر از 1440 دقیقه (24 ساعت) باشد.',
        'api_url.required' => 'لینک API الزامی است.',
        'api_url.url' => 'لینک API باید یک URL معتبر باشد.',
        'repeat_violation.required' => 'تعداد گزارش یکسان الزامی است.',
        'repeat_violation.integer' => 'تعداد گزارش یکسان باید عدد باشد.',
        'repeat_violation.min' => 'تعداد گزارش یکسان باید حداقل 1 باشد.',
        'count_violation.required' => 'تعداد گزارش برای نمایش اقامت‌گران برتر الزامی است.',
        'count_violation.integer' => 'تعداد گزارش باید عدد باشد.',
        'count_violation.min' => 'تعداد گزارش باید حداقل 1 باشد.',
        'max_violation.required' => 'مجموع نمرات منفی الزامی است.',
        'max_violation.integer' => 'مجموع نمرات منفی باید عدد باشد.',
        'max_violation.min' => 'مجموع نمرات منفی باید حداقل 1 باشد.',
        'sms_delay_before_start.required' => 'تاخیر قبل از شروع ارسال الزامی است.',
        'sms_delay_before_start.integer' => 'تاخیر قبل از شروع ارسال باید عدد باشد.',
        'sms_delay_before_start.min' => 'تاخیر قبل از شروع ارسال نمی‌تواند منفی باشد.',
        'sms_delay_before_start.max' => 'تاخیر قبل از شروع ارسال نمی‌تواند بیشتر از 60 ثانیه باشد.',
        'sms_delay_between_messages.required' => 'تاخیر بین هر پیامک الزامی است.',
        'sms_delay_between_messages.integer' => 'تاخیر بین هر پیامک باید عدد باشد.',
        'sms_delay_between_messages.min' => 'تاخیر بین هر پیامک نمی‌تواند منفی باشد.',
        'sms_delay_between_messages.max' => 'تاخیر بین هر پیامک نمی‌تواند بیشتر از 5000 میلی‌ثانیه باشد.',
    ];

    public function mount()
    {
        $settings = Settings::getSettings();
        $this->refresh_interval = $settings->refresh_interval ?? 5;
        $this->api_url = $settings->api_url ?? 'http://atlas2.test/api/residents';
        $this->sms_delay_before_start = $settings->sms_delay_before_start ?? 2;
        $this->sms_delay_between_messages = $settings->sms_delay_between_messages ?? 200;
        
        // بارگذاری تنظیمات گزارش تخلفات از constants
        $repeatViolation = \App\Models\Constant::where('key', 'repeat_violation')->first();
        $this->repeat_violation = $repeatViolation ? (int)$repeatViolation->value : 3;
        
        $countViolation = \App\Models\Constant::where('key', 'count_violation')->first();
        $this->count_violation = $countViolation ? (int)$countViolation->value : 5;
        
        $maxViolation = \App\Models\Constant::where('key', 'max_violation')->first();
        $this->max_violation = $maxViolation ? (int)$maxViolation->value : 10;
    }

    public function save()
    {
        $this->validate();

        Settings::updateSettings([
            'refresh_interval' => $this->refresh_interval,
            'api_url' => $this->api_url,
            'sms_delay_before_start' => $this->sms_delay_before_start,
            'sms_delay_between_messages' => $this->sms_delay_between_messages,
        ]);

        // ذخیره تنظیمات گزارش تخلفات در constants
        \App\Models\Constant::updateOrCreate(
            ['key' => 'repeat_violation'],
            ['value' => (string)$this->repeat_violation, 'type' => 'number', 'description' => 'تعداد گزارش یکسان برای نمایش در اقامت‌گران با تخلف‌های تکرارای یکسان']
        );
        
        \App\Models\Constant::updateOrCreate(
            ['key' => 'count_violation'],
            ['value' => (string)$this->count_violation, 'type' => 'number', 'description' => 'تعداد گزارش برای نمایش در اقامت‌گران با تعداد گزارش بالا']
        );
        
        \App\Models\Constant::updateOrCreate(
            ['key' => 'max_violation'],
            ['value' => (string)$this->max_violation, 'type' => 'number', 'description' => 'مجموع نمرات منفی برای نمایش در اقامت‌گران برتر']
        );

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
