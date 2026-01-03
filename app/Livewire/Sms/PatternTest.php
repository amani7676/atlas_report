<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Services\MelipayamakService;
use Illuminate\Support\Facades\Log;

class PatternTest extends Component
{
    public $selectedPattern = null;
    public $phone = '';
    public $patternText = '';
    public $variables = [];
    public $variableValues = [];
    public $loading = false;
    public $result = null;
    public $showResult = false;
    public $previewMessage = ''; // پیش‌نمایش پیام با متغیرهای جایگزین شده
    public $senderNumber = ''; // شماره فرستنده
    public $selectedSenderNumberId = null; // ID شماره فرستنده انتخاب شده
    public $availableSenderNumbers = []; // لیست شماره‌های فرستنده موجود

    public function mount()
    {
        $this->loadSenderNumbers();
    }

    public function loadSenderNumbers()
    {
        $this->availableSenderNumbers = \App\Models\SenderNumber::getActivePatternNumbers();
        
        // اگر شماره‌ای انتخاب نشده، اولین شماره را به عنوان پیش‌فرض انتخاب کن
        if ($this->availableSenderNumbers->count() > 0 && !$this->selectedSenderNumberId) {
            $this->selectedSenderNumberId = $this->availableSenderNumbers->first()->id;
            $this->updateSenderNumber();
        } else {
            // اگر شماره‌ای در دیتابیس نیست، از config استفاده کن
            $this->senderNumber = config('services.melipayamak.pattern_from') 
                                ?? config('services.melipayamak.from') 
                                ?? 'تنظیم نشده';
        }
    }

    public function updatedSelectedSenderNumberId()
    {
        $this->updateSenderNumber();
    }

    public function updateSenderNumber()
    {
        if ($this->selectedSenderNumberId) {
            $senderNumber = \App\Models\SenderNumber::find($this->selectedSenderNumberId);
            if ($senderNumber) {
                $this->senderNumber = $senderNumber->number;
            }
        }
    }

    public function updatedSelectedPattern($value)
    {
        if ($value) {
            $pattern = Pattern::find($value);
            if ($pattern) {
                $this->patternText = $pattern->text;
                $this->extractVariables();
            }
        } else {
            $this->patternText = '';
            $this->variables = [];
            $this->variableValues = [];
            $this->previewMessage = '';
        }
        $this->updatePreview();
    }
    
    public function updatedVariableValues()
    {
        $this->updatePreview();
    }
    
    /**
     * به‌روزرسانی پیش‌نمایش پیام با متغیرهای جایگزین شده
     */
    public function updatePreview()
    {
        $this->previewMessage = '';
        
        if (!$this->patternText || empty($this->variables) || empty($this->variableValues)) {
            return;
        }
        
        try {
            // ساخت پیش‌نمایش پیام با جایگزینی متغیرها
            $previewText = $this->patternText;
            
            // جایگزینی متغیرها در متن - باید به ترتیب {0}, {1}, {2} جایگزین شوند
            preg_match_all('/\{(\d+)\}/', $this->patternText, $matches);
            if (!empty($matches[0])) {
                $usedIndices = array_unique(array_map('intval', $matches[1]));
                sort($usedIndices);
                
                foreach ($usedIndices as $varIndex) {
                    $match = '{' . $varIndex . '}';
                    $value = $this->variableValues[$varIndex] ?? '';
                    
                    if (!empty($value)) {
                        $valueEscaped = htmlspecialchars($value);
                        $previewText = str_replace($match, '<strong style="color: #4361ee; background: #e0e7ff; padding: 2px 6px; border-radius: 3px;">{' . $varIndex . '}: ' . $valueEscaped . '</strong>', $previewText);
                    } else {
                        $previewText = str_replace($match, '<span style="color: #dc3545; background: #ffe0e0; padding: 2px 6px; border-radius: 3px;">{' . $varIndex . '}: [مقدار وارد نشده]</span>', $previewText);
                    }
                }
            }
            
            $this->previewMessage = $previewText;
        } catch (\Exception $e) {
            Log::error('Error updating preview in PatternTest', [
                'error' => $e->getMessage(),
            ]);
            $this->previewMessage = '<span style="color: #dc3545;">خطا در ساخت پیش‌نمایش: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
    }

    protected function extractVariables()
    {
        // پیدا کردن تمام متغیرها در الگو (مثل {0}, {1}, {2})
        preg_match_all('/\{(\d+)\}/', $this->patternText, $matches);
        
        if (empty($matches[1])) {
            $this->variables = [];
            $this->variableValues = [];
            return;
        }

        // بارگذاری متغیرها از دیتابیس
        $patternVariables = PatternVariable::where('is_active', true)
            ->get()
            ->keyBy('code'); // کلید بر اساس کد (مثل {0}, {1})

        $this->variables = [];
        $this->variableValues = [];
        
        $usedIndices = array_unique(array_map('intval', $matches[1]));
        sort($usedIndices);

        foreach ($usedIndices as $index) {
            $code = '{' . $index . '}';
            $variable = $patternVariables->get($code);

            if ($variable) {
                $this->variables[] = [
                    'code' => $code,
                    'index' => $index,
                    'title' => $variable->title,
                    'table_field' => $variable->table_field,
                    'variable_type' => $variable->variable_type,
                ];
                $this->variableValues[$index] = ''; // مقدار پیش‌فرض خالی
            } else {
                // اگر متغیر در دیتابیس پیدا نشد
                $this->variables[] = [
                    'code' => $code,
                    'index' => $index,
                    'title' => 'متغیر ' . $code,
                    'table_field' => '',
                    'variable_type' => 'unknown',
                ];
                $this->variableValues[$index] = '';
            }
        }
        
        // به‌روزرسانی پیش‌نمایش بعد از استخراج متغیرها
        $this->updatePreview();
    }

    public function sendTest()
    {
        $this->validate([
            'selectedPattern' => 'required|exists:patterns,id',
            'phone' => 'required|regex:/^09\d{9}$/',
        ], [
            'selectedPattern.required' => 'لطفاً یک الگو را انتخاب کنید.',
            'selectedPattern.exists' => 'الگوی انتخاب شده معتبر نیست.',
            'phone.required' => 'لطفاً شماره تلفن را وارد کنید.',
            'phone.regex' => 'شماره تلفن باید با 09 شروع شود و 11 رقم باشد.',
        ]);

        $pattern = Pattern::find($this->selectedPattern);
        
        if (!$pattern || !$pattern->pattern_code) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'الگوی انتخاب شده معتبر نیست یا کد الگو ندارد.'
            ]);
            return;
        }

        // ساخت آرایه متغیرها به ترتیب
        $variablesArray = [];
        foreach ($this->variables as $variable) {
            $index = $variable['index'];
            $value = $this->variableValues[$index] ?? '';
            $variablesArray[] = $value;
        }

        $this->loading = true;
        $this->showResult = false;
        $this->result = null;

        try {
            $melipayamakService = new MelipayamakService();
            
            Log::info('Pattern Test - Sending SMS', [
                'pattern_id' => $pattern->id,
                'pattern_code' => $pattern->pattern_code,
                'phone' => $this->phone,
                'variables' => $variablesArray,
                'variables_count' => count($variablesArray),
            ]);

            // دریافت شماره فرستنده و API Key از شماره انتخاب شده
            $senderNumberObj = null;
            $apiKey = null;
            if ($this->selectedSenderNumberId) {
                $senderNumberObj = \App\Models\SenderNumber::find($this->selectedSenderNumberId);
                if ($senderNumberObj) {
                    $apiKey = $senderNumberObj->api_key;
                }
            }

            // استفاده از متد SendByBaseNumber (SOAP API) برای تست
            $result = $melipayamakService->sendByBaseNumber(
                $this->phone,
                $pattern->pattern_code,
                $variablesArray,
                $senderNumberObj ? $senderNumberObj->number : null, // شماره فرستنده
                $apiKey // API Key مرتبط با شماره
            );

            $this->result = $result;
            $this->showResult = true;

            if ($result['success']) {
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'پیامک با موفقیت ارسال شد.',
                ]);
            } else {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => $result['message'] ?? 'خطا در ارسال پیامک',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Pattern Test - Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->result = [
                'success' => false,
                'message' => 'خطا در ارسال: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
            $this->showResult = true;

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ارسال پیامک: ' . $e->getMessage(),
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        // فقط الگوهایی که تایید شده، فعال، دارای pattern_code و برایشان گزارش ست شده نمایش داده می‌شوند
        $patterns = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code')
            ->whereHas('reports', function ($query) {
                $query->where('report_pattern.is_active', true);
            })
            ->orderBy('title')
            ->get();

        return view('livewire.sms.pattern-test', [
            'patterns' => $patterns,
        ]);
    }
}


