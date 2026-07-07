<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Constant;

class CardThresholds extends Component
{
    public $yellowThreshold;
    public $redThreshold;
    public $yellowViolationCountThreshold;
    public $redViolationCountThreshold;
    public $successMessage = '';

    public function mount()
    {
        $this->loadThresholds();
    }

    public function loadThresholds()
    {
        $yellowConstant = Constant::where('key', 'yellow_card_threshold')->first();
        $redConstant = Constant::where('key', 'red_card_threshold')->first();
        $yellowViolationCountConstant = Constant::where('key', 'yellow_violation_count_threshold')->first();
        $redViolationCountConstant = Constant::where('key', 'red_violation_count_threshold')->first();

        $this->yellowThreshold = $yellowConstant ? $yellowConstant->value : 20;
        $this->redThreshold = $redConstant ? $redConstant->value : 30;
        $this->yellowViolationCountThreshold = $yellowViolationCountConstant ? $yellowViolationCountConstant->value : 3;
        $this->redViolationCountThreshold = $redViolationCountConstant ? $redViolationCountConstant->value : 5;
    }

    public function saveThresholds()
    {
        $this->validate([
            'yellowThreshold' => 'required|integer|min:1|max:100',
            'redThreshold' => 'required|integer|min:1|max:100',
            'yellowViolationCountThreshold' => 'required|integer|min:1|max:50',
            'redViolationCountThreshold' => 'required|integer|min:1|max:50',
        ], [
            'yellowThreshold.required' => 'آستانه کارت زرد الزامی است',
            'yellowThreshold.integer' => 'آستانه کارت زرد باید عدد باشد',
            'yellowThreshold.min' => 'آستانه کارت زرد باید حداقل 1 باشد',
            'yellowThreshold.max' => 'آستانه کارت زرد نباید بیشتر از 100 باشد',
            'redThreshold.required' => 'آستانه کارت قرمز الزامی است',
            'redThreshold.integer' => 'آستانه کارت قرمز باید عدد باشد',
            'redThreshold.min' => 'آستانه کارت قرمز باید حداقل 1 باشد',
            'redThreshold.max' => 'آستانه کارت قرمز نباید بیشتر از 100 باشد',
            'yellowViolationCountThreshold.required' => 'آستانه تعداد تخلف کارت زرد الزامی است',
            'yellowViolationCountThreshold.integer' => 'آستانه تعداد تخلف کارت زرد باید عدد باشد',
            'yellowViolationCountThreshold.min' => 'آستانه تعداد تخلف کارت زرد باید حداقل 1 باشد',
            'yellowViolationCountThreshold.max' => 'آستانه تعداد تخلف کارت زرد نباید بیشتر از 50 باشد',
            'redViolationCountThreshold.required' => 'آستانه تعداد تخلف کارت قرمز الزامی است',
            'redViolationCountThreshold.integer' => 'آستانه تعداد تخلف کارت قرمز باید عدد باشد',
            'redViolationCountThreshold.min' => 'آستانه تعداد تخلف کارت قرمز باید حداقل 1 باشد',
            'redViolationCountThreshold.max' => 'آستانه تعداد تخلف کارت قرمز نباید بیشتر از 50 باشد',
        ]);

        // بررسی اینکه آستانه قرمز باید بزرگتر یا مساوی زرد باشد
        if ($this->redThreshold < $this->yellowThreshold) {
            $this->addError('redThreshold', 'آستانه کارت قرمز باید بزرگتر یا مساوی آستانه کارت زرد باشد');
            return;
        }

        // بررسی اینکه آستانه تعداد تخلف قرمز باید بزرگتر یا مساوی زرد باشد
        if ($this->redViolationCountThreshold < $this->yellowViolationCountThreshold) {
            $this->addError('redViolationCountThreshold', 'آستانه تعداد تخلف کارت قرمز باید بزرگتر یا مساوی آستانه تعداد تخلف کارت زرد باشد');
            return;
        }

        try {
            // ذخیره یا ایجاد آستانه کارت زرد
            Constant::updateOrCreate(
                ['key' => 'yellow_card_threshold'],
                [
                    'value' => $this->yellowThreshold,
                    'description' => 'آستانه امتیاز برای کارت زرد'
                ]
            );

            // ذخیره یا ایجاد آستانه کارت قرمز
            Constant::updateOrCreate(
                ['key' => 'red_card_threshold'],
                [
                    'value' => $this->redThreshold,
                    'description' => 'آستانه امتیاز برای کارت قرمز'
                ]
            );

            // ذخیره یا ایجاد آستانه تعداد تخلف کارت زرد
            Constant::updateOrCreate(
                ['key' => 'yellow_violation_count_threshold'],
                [
                    'value' => $this->yellowViolationCountThreshold,
                    'description' => 'آستانه تعداد تخلف برای کارت زرد'
                ]
            );

            // ذخیره یا ایجاد آستانه تعداد تخلف کارت قرمز
            Constant::updateOrCreate(
                ['key' => 'red_violation_count_threshold'],
                [
                    'value' => $this->redViolationCountThreshold,
                    'description' => 'آستانه تعداد تخلف برای کارت قرمز'
                ]
            );

            $this->successMessage = 'تنظیمات با موفقیت ذخیره شد!';

            // پاک کردن کش برای اعمال تغییرات
            \Artisan::call('cache:clear');

            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت',
                'message' => 'آستانه‌های کارت‌ها با موفقیت به‌روزرسانی شد.',
                'duration' => 3000,
            ]);

        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا',
                'message' => 'خطا در ذخیره تنظیمات: ' . $e->getMessage(),
                'duration' => 5000,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.card-thresholds');
    }
}
