<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Constant;

class CardThresholds extends Component
{
    public $yellowThreshold;
    public $redThreshold;
    public $successMessage = '';

    public function mount()
    {
        $this->loadThresholds();
    }

    public function loadThresholds()
    {
        $yellowConstant = Constant::where('key', 'yellow_card_threshold')->first();
        $redConstant = Constant::where('key', 'red_card_threshold')->first();

        $this->yellowThreshold = $yellowConstant ? $yellowConstant->value : 20;
        $this->redThreshold = $redConstant ? $redConstant->value : 30;
    }

    public function saveThresholds()
    {
        $this->validate([
            'yellowThreshold' => 'required|integer|min:1|max:100',
            'redThreshold' => 'required|integer|min:1|max:100',
        ], [
            'yellowThreshold.required' => 'آستانه کارت زرد الزامی است',
            'yellowThreshold.integer' => 'آستانه کارت زرد باید عدد باشد',
            'yellowThreshold.min' => 'آستانه کارت زرد باید حداقل 1 باشد',
            'yellowThreshold.max' => 'آستانه کارت زرد نباید بیشتر از 100 باشد',
            'redThreshold.required' => 'آستانه کارت قرمز الزامی است',
            'redThreshold.integer' => 'آستانه کارت قرمز باید عدد باشد',
            'redThreshold.min' => 'آستانه کارت قرمز باید حداقل 1 باشد',
            'redThreshold.max' => 'آستانه کارت قرمز نباید بیشتر از 100 باشد',
        ]);

        // بررسی اینکه آستانه قرمز باید بزرگتر یا مساوی زرد باشد
        if ($this->redThreshold < $this->yellowThreshold) {
            $this->addError('redThreshold', 'آستانه کارت قرمز باید بزرگتر یا مساوی آستانه کارت زرد باشد');
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
