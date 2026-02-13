<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Settings;

class Index extends Component
{
    public $api_url = '';
    public $sms_delay_before_start = 2; // تاخیر قبل از شروع ارسال (ثانیه)
    public $sms_delay_between_messages = 200; // تاخیر بین هر پیامک (میلی‌ثانیه)
    public $repeat_violation = 3; // تعداد گزارش یکسان برای نمایش
    public $yellow_card_threshold = 15; // امتیاز برای کارت زرد
    public $red_card_threshold = 25; // امتیاز برای کارت قرمز

    protected function rules()
    {
        return [
            'api_url' => 'required|url',
            'sms_delay_before_start' => 'required|integer|min:0|max:60',
            'sms_delay_between_messages' => 'required|integer|min:0|max:5000',
            'repeat_violation' => 'required|integer|min:1',
            'yellow_card_threshold' => 'required|integer|min:1',
            'red_card_threshold' => 'required|integer|min:1',
        ];
    }

    protected $messages = [
        'api_url.required' => 'لینک API الزامی است.',
        'api_url.url' => 'لینک API باید یک URL معتبر باشد.',
        'repeat_violation.required' => 'تعداد گزارش یکسان الزامی است.',
        'repeat_violation.integer' => 'تعداد گزارش یکسان باید عدد باشد.',
        'repeat_violation.min' => 'تعداد گزارش یکسان باید حداقل 1 باشد.',
        'yellow_card_threshold.required' => 'امتیاز کارت زرد الزامی است.',
        'yellow_card_threshold.integer' => 'امتیاز کارت زرد باید عدد باشد.',
        'yellow_card_threshold.min' => 'امتیاز کارت زرد باید حداقل 1 باشد.',
        'red_card_threshold.required' => 'امتیاز کارت قرمز الزامی است.',
        'red_card_threshold.integer' => 'امتیاز کارت قرمز باید عدد باشد.',
        'red_card_threshold.min' => 'امتیاز کارت قرمز باید حداقل 1 باشد.',
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
        $this->api_url = $settings->api_url ?? 'http://atlas2.test/api/residents';
        $this->sms_delay_before_start = $settings->sms_delay_before_start ?? 2;
        $this->sms_delay_between_messages = $settings->sms_delay_between_messages ?? 200;
        
        // بارگذاری تنظیمات گزارش تخلفات از constants
        $repeatViolation = \App\Models\Constant::where('key', 'repeat_violation')->first();
        $this->repeat_violation = $repeatViolation ? (int)$repeatViolation->value : 3;
        
        // بارگذاری تنظیمات کارت‌ها از constants
        $yellowCard = \App\Models\Constant::where('key', 'yellow_card_threshold')->first();
        $this->yellow_card_threshold = $yellowCard ? (int)$yellowCard->value : 15;
        
        $redCard = \App\Models\Constant::where('key', 'red_card_threshold')->first();
        $this->red_card_threshold = $redCard ? (int)$redCard->value : 25;
    }

    public function save()
    {
        $this->validate();

        Settings::updateSettings([
            'api_url' => $this->api_url,
            'sms_delay_before_start' => $this->sms_delay_before_start,
            'sms_delay_between_messages' => $this->sms_delay_between_messages,
        ]);

        // ذخیره تنظیمات گزارش تخلفات در constants
        \App\Models\Constant::updateOrCreate(
            ['key' => 'repeat_violation'],
            ['value' => (string)$this->repeat_violation, 'type' => 'number', 'description' => 'تعداد گزارش یکسان برای نمایش در اقامت‌گران با تخلف‌های تکرارای یکسان']
        );
        
        // ذخیره تنظیمات کارت‌ها در constants
        \App\Models\Constant::updateOrCreate(
            ['key' => 'yellow_card_threshold'],
            ['value' => (string)$this->yellow_card_threshold, 'type' => 'number', 'description' => 'امتیاز لازم برای دریافت کارت زرد']
        );
        
        \App\Models\Constant::updateOrCreate(
            ['key' => 'red_card_threshold'],
            ['value' => (string)$this->red_card_threshold, 'type' => 'number', 'description' => 'امتیاز لازم برای دریافت کارت قرمز']
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
