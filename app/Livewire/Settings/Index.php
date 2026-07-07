<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Settings;
use App\Models\Report;

class Index extends Component
{
    public $api_url = '';
    public $sms_delay_before_start = 2; // تاخیر قبل از شروع ارسال (ثانیه)
    public $sms_delay_between_messages = 200; // تاخیر بین هر پیامک (میلی‌ثانیه)
    public $yellow_card_threshold = 15; // امتیاز برای کارت زرد
    public $red_card_threshold = 25; // امتیاز برای کارت قرمز
    public $yellow_violation_count_threshold = 3; // تعداد تخلف یکسان برای کارت زرد
    public $red_violation_count_threshold = 5; // تعداد تخلف یکسان برای کارت قرمز
    public $excluded_reports = []; // لیست گزارش‌های مستثنی شده
    public $reports = []; // لیست تمام گزارش‌ها

    protected function rules()
    {
        return [
            'api_url' => 'required|url',
            'sms_delay_before_start' => 'required|integer|min:0|max:60',
            'sms_delay_between_messages' => 'required|integer|min:0|max:5000',
            'yellow_card_threshold' => 'required|integer|min:1',
            'red_card_threshold' => 'required|integer|min:1',
            'yellow_violation_count_threshold' => 'required|integer|min:1',
            'red_violation_count_threshold' => 'required|integer|min:1',
            'excluded_reports' => 'array',
            'excluded_reports.*' => 'integer|exists:reports,id',
        ];
    }

    protected $messages = [
        'api_url.required' => 'لینک API الزامی است.',
        'api_url.url' => 'لینک API باید یک URL معتبر باشد.',
        'yellow_card_threshold.required' => 'امتیاز کارت زیر الزامی است.',
        'yellow_card_threshold.integer' => 'امتیاز کارت زرد باید عدد باشد.',
        'yellow_card_threshold.min' => 'امتیاز کارت زرد باید حداقل 1 باشد.',
        'red_card_threshold.required' => 'امتیاز کارت قرمز الزامی است.',
        'red_card_threshold.integer' => 'امتیاز کارت قرمز باید عدد باشد.',
        'red_card_threshold.min' => 'امتیاز کارت قرمز باید حداقل 1 باشد.',
        'yellow_violation_count_threshold.required' => 'تعداد تخلف کارت زرد الزامی است.',
        'yellow_violation_count_threshold.integer' => 'تعداد تخلف کارت زرد باید عدد باشد.',
        'yellow_violation_count_threshold.min' => 'تعداد تخلف کارت زرد باید حداقل 1 باشد.',
        'red_violation_count_threshold.required' => 'تعداد تخلف کارت قرمز الزامی است.',
        'red_violation_count_threshold.integer' => 'تعداد تخلف کارت قرمز باید عدد باشد.',
        'red_violation_count_threshold.min' => 'تعداد تخلف کارت قرمز باید حداقل 1 باشد.',
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
        
        // بارگذاری تنظیمات کارت‌ها از constants
        $yellowCard = \App\Models\Constant::where('key', 'yellow_card_threshold')->first();
        $this->yellow_card_threshold = $yellowCard ? (int)$yellowCard->value : 15;
        
        $redCard = \App\Models\Constant::where('key', 'red_card_threshold')->first();
        $this->red_card_threshold = $redCard ? (int)$redCard->value : 25;
        
        $yellowViolationCount = \App\Models\Constant::where('key', 'yellow_violation_count_threshold')->first();
        $this->yellow_violation_count_threshold = $yellowViolationCount ? (int)$yellowViolationCount->value : 3;
        
        $redViolationCount = \App\Models\Constant::where('key', 'red_violation_count_threshold')->first();
        $this->red_violation_count_threshold = $redViolationCount ? (int)$redViolationCount->value : 5;
        
        // بارگذاری لیست تمام گزارش‌ها
        $this->reports = Report::with('category')
            ->orderBy('title')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'title' => $report->title,
                    'category_name' => $report->category ? $report->category->name : 'بدون دسته',
                    'negative_score' => $report->negative_score,
                ];
            })
            ->toArray();
        
        // بارگذاری گزارش‌های مستثنی شده از constants
        $excludedReportsConstant = \App\Models\Constant::where('key', 'excluded_reports')->first();
        if ($excludedReportsConstant && $excludedReportsConstant->value) {
            $this->excluded_reports = json_decode($excludedReportsConstant->value, true) ?? [];
        }
    }

    public function save()
    {
        $this->validate();

        Settings::updateSettings([
            'api_url' => $this->api_url,
            'sms_delay_before_start' => $this->sms_delay_before_start,
            'sms_delay_between_messages' => $this->sms_delay_between_messages,
        ]);

        // ذخیره تنظیمات کارت‌ها در constants
        \App\Models\Constant::updateOrCreate(
            ['key' => 'yellow_card_threshold'],
            ['value' => (string)$this->yellow_card_threshold, 'type' => 'number', 'description' => 'امتیاز لازم برای دریافت کارت زرد']
        );
        
        \App\Models\Constant::updateOrCreate(
            ['key' => 'red_card_threshold'],
            ['value' => (string)$this->red_card_threshold, 'type' => 'number', 'description' => 'امتیاز لازم برای دریافت کارت قرمز']
        );
        
        \App\Models\Constant::updateOrCreate(
            ['key' => 'yellow_violation_count_threshold'],
            ['value' => (string)$this->yellow_violation_count_threshold, 'type' => 'number', 'description' => 'تعداد تخلف یکسان برای کارت زرد']
        );
        
        \App\Models\Constant::updateOrCreate(
            ['key' => 'red_violation_count_threshold'],
            ['value' => (string)$this->red_violation_count_threshold, 'type' => 'number', 'description' => 'تعداد تخلف یکسان برای کارت قرمز']
        );
        
        // ذخیره گزارش‌های مستثنی شده در constants
        \App\Models\Constant::updateOrCreate(
            ['key' => 'excluded_reports'],
            ['value' => json_encode($this->excluded_reports), 'type' => 'string', 'description' => 'لیست گزارش‌های مستثنی شده از محاسبه تخلفات']
        );

        // به‌روزرسانی مجدد متغیرها برای نمایش صحیح
        $this->refreshExcludedReports();

        $excludedCount = count($this->excluded_reports);
        $message = 'تنظیمات با موفقیت ذخیره شد.';
        if ($excludedCount > 0) {
            $message .= " {$excludedCount} گزارش مستثنی شده ذخیره گردید.";
        }

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => $message,
            'duration' => 3000,
        ]);

        // ارسال event برای به‌روزرسانی JavaScript
        $this->dispatch('settings-updated');
    }
    
    /**
     * به‌روزرسانی مجدد گزارش‌های مستثنی شده از دیتابیس
     */
    private function refreshExcludedReports()
    {
        $excludedReportsConstant = \App\Models\Constant::where('key', 'excluded_reports')->first();
        if ($excludedReportsConstant && $excludedReportsConstant->value) {
            $this->excluded_reports = json_decode($excludedReportsConstant->value, true) ?? [];
        } else {
            $this->excluded_reports = [];
        }
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
