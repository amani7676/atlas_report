<?php

namespace App\Livewire\Residents;

use App\Models\Resident;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;
use App\Services\ResidentService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class ExpiredToday extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 20;
    public $selectedResidents = [];
    public $selectAll = false;
    
    // Computed property برای بررسی اینکه آیا می‌توان ارسال کرد
    public function getCanSendProperty()
    {
        $hasSelection = !empty($this->selectedResidents) && 
                       is_array($this->selectedResidents) && 
                       count($this->selectedResidents) > 0;
        $hasPattern = !empty($this->selectedPattern);
        return $hasSelection && $hasPattern;
    }
    
    public function mount()
    {
        // اطمینان از اینکه selectedResidents همیشه یک array خالی است
        $this->selectedResidents = [];
        $this->selectAll = false;
        $this->loadPatterns();
    }

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
        // فقط pagination را reset می‌کنیم تا داده‌های جدید نمایش داده شوند
        $this->resetPage();
    }
    
    public function clearSelection()
    {
        // پاک کردن همه انتخاب‌ها
        $this->selectedResidents = [];
        $this->selectAll = false;
    }

    // Pattern SMS properties
    public $selectedPattern = null;
    public $patterns = [];
    
    // Sending progress
    public $isSending = false;
    public $sendingProgress = [
        'total' => 0,
        'sent' => 0,
        'failed' => 0,
        'current' => null,
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatedPerPage()
    {
        // وقتی تعداد صفحه تغییر می‌کند، selectedResidents را پاک نکنیم
        // فقط selectAll را به‌روزرسانی می‌کنیم
        $this->updateSelectAllState();
    }

    public function toggleSelectAll()
    {
        // اطمینان از اینکه selectedResidents یک array است
        if (!is_array($this->selectedResidents)) {
            $this->selectedResidents = [];
        }
        
        // دریافت همه اقامت‌گران فیلتر شده در صفحه فعلی
        $residents = $this->getFilteredResidentsQuery()->get();
        $activeResidentIds = [];
        
        // فقط اقامت‌گران فعال را جمع‌آوری می‌کنیم
        foreach ($residents as $resident) {
            $disabledInfo = $this->isResidentDisabled($resident);
            if (!$disabledInfo['disabled']) {
                $activeResidentIds[] = (int)$resident->id;
            }
        }
        
        if (empty($activeResidentIds)) {
            // اگر هیچ اقامت‌گر فعالی وجود ندارد، هیچ کاری انجام نمی‌دهیم
            $this->selectAll = false;
            return;
        }
        
        // تبدیل selectedResidents به array از integer
        $currentSelected = array_map('intval', $this->selectedResidents);
        
        // بررسی اینکه آیا همه اقامت‌گران فعال در صفحه فعلی انتخاب شده‌اند
        $allSelected = count($activeResidentIds) === count($currentSelected) && 
                      empty(array_diff($activeResidentIds, $currentSelected));
        
        if ($allSelected) {
            // اگر همه انتخاب شده‌اند، همه را deselect می‌کنیم
            // حذف فقط اقامت‌گران صفحه فعلی از selectedResidents
            $this->selectedResidents = array_values(array_diff($currentSelected, $activeResidentIds));
            $this->selectAll = false;
        } else {
            // اگر همه انتخاب نشده‌اند، همه را select می‌کنیم
            // اضافه کردن اقامت‌گران صفحه فعلی به selectedResidents (بدون تکرار)
            $merged = array_unique(array_merge($currentSelected, $activeResidentIds));
            $this->selectedResidents = array_values($merged);
            $this->selectAll = true;
        }
    }

    public function updatedSelectedResidents()
    {
        // اطمینان از اینکه selectedResidents یک array است
        if (!is_array($this->selectedResidents)) {
            $this->selectedResidents = [];
            $this->dispatch('updateSendButton');
            return;
        }
        
        // فیلتر کردن اقامت‌گران غیرفعال از selectedResidents
        $filtered = [];
        foreach ($this->selectedResidents as $residentId) {
            $residentId = (int)$residentId;
            $resident = Resident::find($residentId);
            if ($resident) {
                $disabledInfo = $this->isResidentDisabled($resident);
                if (!$disabledInfo['disabled']) {
                    $filtered[] = $residentId;
                }
            }
        }
        
        // تبدیل به array از integer و حذف تکرارها
        $this->selectedResidents = array_values(array_unique(array_map('intval', $filtered)));
        
        // به‌روزرسانی selectAll
        $this->updateSelectAllState();
        
        // ارسال event برای به‌روزرسانی دکمه
        $this->dispatch('updateSendButton');
    }
    
    public function updatedSelectedPattern()
    {
        // ارسال event برای به‌روزرسانی دکمه
        $this->dispatch('updateSendButton');
    }
    
    protected function updateSelectAllState()
    {
        // اطمینان از اینکه selectedResidents یک array است
        if (!is_array($this->selectedResidents)) {
            $this->selectedResidents = [];
        }
        
        // دریافت همه اقامت‌گران فیلتر شده
        $allFilteredResidents = $this->getFilteredResidentsQuery()->get();
        $activeResidentIds = [];
        
        foreach ($allFilteredResidents as $resident) {
            $disabledInfo = $this->isResidentDisabled($resident);
            if (!$disabledInfo['disabled']) {
                $activeResidentIds[] = (int)$resident->id;
            }
        }
        
        // تبدیل selectedResidents به array از integer
        $currentSelected = array_map('intval', $this->selectedResidents);
        
        // بررسی اینکه آیا همه اقامت‌گران فعال انتخاب شده‌اند
        $allActiveSelected = !empty($activeResidentIds) && 
                            count($activeResidentIds) === count($currentSelected) && 
                            empty(array_diff($activeResidentIds, $currentSelected));
        
        $this->selectAll = $allActiveSelected;
    }

    /**
     * محاسبه تعداد روزهای گذشته از سررسید نسبت به امروز
     * مثلاً: امروز = 0، 2 روز قبل = 2، 3 روز قبل = 3
     */
    public function getDaysPastDue($paymentDateJalali)
    {
        if (!$paymentDateJalali || empty(trim($paymentDateJalali))) {
            return 0;
        }

        try {
            // استفاده از کتابخانه Jalali برای محاسبه دقیق
            if (class_exists(\Morilog\Jalali\Jalalian::class)) {
                // استفاده از fromFormat برای ساخت Jalalian از رشته تاریخ شمسی
                $paymentJalali = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', trim($paymentDateJalali));
                $paymentCarbon = $paymentJalali->toCarbon()->startOfDay();
                $todayCarbon = now()->startOfDay();
                
                // محاسبه تفاوت روزها
                // اگر تاریخ سررسید بعد از امروز باشد (که نباید اتفاق بیفتد چون فیلتر شده)، 0 برمی‌گردانیم
                if ($paymentCarbon->gt($todayCarbon)) {
                    return 0;
                }
                
                // محاسبه تعداد روزهای گذشته از سررسید
                // diffInDays وقتی تاریخ قبل از امروز باشد، عدد منفی برمی‌گرداند
                // پس باید از abs استفاده کنیم
                $daysDiff = abs($todayCarbon->diffInDays($paymentCarbon, false));
                
                return $daysDiff;
            }
        } catch (\Exception $e) {
            // در صورت خطا، از روش تقریبی استفاده می‌کنیم
            \Log::debug('Error calculating days past due', [
                'payment_date_jalali' => $paymentDateJalali,
                'error' => $e->getMessage()
            ]);
        }

        // روش تقریبی: استفاده از مقایسه عددی
        $todayJalali = null;
        if (class_exists(\Morilog\Jalali\Jalalian::class)) {
            $todayJalali = \Morilog\Jalali\Jalalian::fromCarbon(now())->format('Y/m/d');
        } else {
            $todayJalali = now()->format('Y/m/d');
        }

        $todayNumeric = (int) str_replace('/', '', $todayJalali);
        $paymentNumeric = (int) str_replace('/', '', $paymentDateJalali);
        
        // اگر تاریخ سررسید بعد از امروز باشد، 0 برمی‌گردانیم
        if ($paymentNumeric > $todayNumeric) {
            return 0;
        }

        // محاسبه تقریبی روزها
        $todayParts = explode('/', $todayJalali);
        $paymentParts = explode('/', $paymentDateJalali);
        
        if (count($todayParts) === 3 && count($paymentParts) === 3) {
            $todayYear = (int)$todayParts[0];
            $todayMonth = (int)$todayParts[1];
            $todayDay = (int)$todayParts[2];
            
            $paymentYear = (int)$paymentParts[0];
            $paymentMonth = (int)$paymentParts[1];
            $paymentDay = (int)$paymentParts[2];
            
            // محاسبه تقریبی: تفاوت سال * 365 + تفاوت ماه * 30 + تفاوت روز
            $yearDiff = $todayYear - $paymentYear;
            $monthDiff = $todayMonth - $paymentMonth;
            $dayDiff = $todayDay - $paymentDay;
            
            $daysPast = $yearDiff * 365 + $monthDiff * 30 + $dayDiff;
            
            // اگر منفی باشد، 0 برمی‌گردانیم
            return max(0, $daysPast);
        }

        return 0;
    }

    public function loadPatterns()
    {
        // فقط الگوهایی که فعال، تایید شده و دارای pattern_code هستند
        $this->patterns = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code')
            ->orderBy('title')
            ->get();
    }


    public function sendPatternSms()
    {
        // اعتبارسنجی
        if (!$this->selectedPattern) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً یک الگو را انتخاب کنید.'
            ]);
            return;
        }

        if (empty($this->selectedResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک اقامت‌گر را انتخاب کنید.'
            ]);
            return;
        }

        $pattern = Pattern::find($this->selectedPattern);
        if (!$pattern || !$pattern->pattern_code) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'الگوی انتخاب شده معتبر نیست یا کد الگو ندارد.'
            ]);
            return;
        }

        // دریافت اقامت‌گران انتخاب شده
        $selectedResidents = Resident::whereIn('id', $this->selectedResidents)
            ->whereNotNull('resident_phone')
            ->where('resident_phone', '!=', '')
            ->get();

        if ($selectedResidents->isEmpty()) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'هیچ اقامت‌گری با شماره تلفن در لیست انتخاب شده یافت نشد.'
            ]);
            return;
        }

        // فیلتر کردن اقامت‌گران غیرفعال
        $activeResidents = [];
        $disabledResidents = [];
        
        foreach ($selectedResidents as $resident) {
            $disabledInfo = $this->isResidentDisabled($resident);
            if ($disabledInfo['disabled']) {
                $disabledResidents[] = $resident->resident_full_name . ' (' . $disabledInfo['reason'] . ')';
            } else {
                $activeResidents[] = $resident;
            }
        }

        if (empty($activeResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'هیچ اقامت‌گر فعالی برای ارسال پیامک وجود ندارد. ' . implode(', ', $disabledResidents)
            ]);
            return;
        }

        if (!empty($disabledResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'توجه!',
                'text' => 'تعداد ' . count($disabledResidents) . ' اقامت‌گر به دلیل عدم تطابق تاریخ سررسید با نوت، از ارسال حذف شدند.'
            ]);
        }

        // دریافت API Key از config
        $apiKey = config('services.melipayamak.console_api_key');

        $melipayamakService = new MelipayamakService();
        $residentService = new ResidentService();
        
        // Reset progress
        $this->isSending = true;
        $this->sendingProgress = [
            'total' => count($activeResidents),
            'sent' => 0,
            'failed' => 0,
            'current' => null,
        ];

        // ارسال به صورت تکی در حلقه (فقط اقامت‌گران فعال)
        foreach ($activeResidents as $resident) {
            if (empty($resident->resident_phone)) {
                $this->sendingProgress['failed']++;
                continue;
            }

            $this->sendingProgress['current'] = $resident->resident_full_name ?? 'بدون نام';
            $this->dispatch('$refresh');
            
            try {
                // دریافت اطلاعات کامل resident از API
                $residentApiData = null;
                if ($resident->resident_id) {
                    try {
                        $residentApiData = $residentService->getResidentById($resident->resident_id);
                    } catch (\Exception $e) {
                        Log::warning('Failed to fetch resident data from API', [
                            'resident_id' => $resident->resident_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // ساخت داده‌های resident برای استخراج متغیرها
                $residentData = [
                    'id' => $resident->resident_id ?? $resident->id,
                    'db_id' => $resident->id,
                    'resident_id' => $resident->resident_id ?? $resident->id,
                    'resident_name' => $resident->resident_full_name,
                    'name' => $resident->resident_full_name,
                    'phone' => $resident->resident_phone,
                    'unit_id' => $resident->unit_id ?? null,
                    'unit_name' => $resident->unit_name ?? '',
                    'room_id' => $resident->room_id ?? null,
                    'room_name' => $resident->room_name ?? '',
                    'bed_id' => $resident->bed_id ?? null,
                    'bed_name' => $resident->bed_name ?? '',
                ];

                // استخراج متغیرها از متن الگو
                $variables = $this->extractPatternVariables($pattern->text, $residentData, $residentApiData);

                // اطمینان از اینکه pattern_code عدد است
                $bodyId = (int)$pattern->pattern_code;
                
                // اطمینان از اینکه variables یک آرایه است
                if (!is_array($variables)) {
                    $variables = [];
                }

                // ایجاد رکورد در جدول sms_message_residents
                $smsMessageResident = SmsMessageResident::create([
                    'sms_message_id' => null,
                    'report_id' => null,
                    'pattern_id' => $pattern->id,
                    'is_pattern' => true,
                    'pattern_variables' => implode(';', $variables),
                    'resident_id' => $resident->id,
                    'resident_name' => $resident->resident_full_name,
                    'phone' => $resident->resident_phone,
                    'title' => $pattern->title,
                    'description' => $pattern->text,
                    'status' => 'pending',
                ]);

                // ارسال پیامک با الگو - استفاده از استراتژی مشابه GroupSms
                $result = null;
                
                // بررسی اینکه SOAP extension فعال است
                if (extension_loaded('soap')) {
                    Log::info('ExpiredToday - Trying SOAP API first');
                    
                    // استفاده از SOAP API
                    $soapResult = $melipayamakService->sendByBaseNumber(
                        $resident->resident_phone,
                        $bodyId,
                        $variables,
                        null,
                        null
                    );
                    
                    if ($soapResult['success']) {
                        $result = $soapResult;
                        Log::info('ExpiredToday - SOAP API succeeded');
                    } else {
                        Log::warning('ExpiredToday - SOAP API failed, trying REST API', [
                            'soap_error' => $soapResult['message'] ?? 'Unknown error',
                        ]);
                    }
                } else {
                    Log::warning('ExpiredToday - SOAP extension not loaded, using REST API');
                }
                
                // اگر SOAP استفاده نشد یا خطا داد، از REST API استفاده می‌کنیم
                if (!$result || !$result['success']) {
                    $restApiKey = $apiKey;
                    if (empty($restApiKey) || strlen($restApiKey) < 20) {
                        $restApiKey = config('services.melipayamak.console_api_key');
                    }
                    
                    Log::info('ExpiredToday - Using REST API (console) with API key', [
                        'api_key_length' => $restApiKey ? strlen($restApiKey) : 0,
                        'api_key_preview' => $restApiKey ? substr($restApiKey, 0, 8) . '...' : 'empty',
                    ]);
                    
                    $restResult = $melipayamakService->sendByBaseNumber2(
                        $resident->resident_phone,
                        $bodyId,
                        $variables,
                        null,
                        $restApiKey
                    );
                    
                    $result = $restResult;
                }

                if ($result['success']) {
                    $smsMessageResident->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'response_code' => $result['response_code'] ?? null,
                        'error_message' => null,
                    ]);
                    $this->sendingProgress['sent']++;
                } else {
                    $errorMessage = $result['message'] ?? 'خطای نامشخص';
                    $smsMessageResident->update([
                        'status' => 'failed',
                        'error_message' => $errorMessage,
                        'response_code' => $result['response_code'] ?? null,
                    ]);
                    $this->sendingProgress['failed']++;
                    
                    // نمایش خطا برای اولین خطا
                    if ($this->sendingProgress['failed'] === 1) {
                        $this->dispatch('showAlert', [
                            'type' => 'error',
                            'title' => 'خطا در ارسال!',
                            'text' => 'خطا در ارسال به ' . ($resident->resident_full_name ?? 'نامشخص') . ': ' . $errorMessage,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending SMS to resident', [
                    'resident_id' => $resident->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->sendingProgress['failed']++;
                
                if ($this->sendingProgress['failed'] === 1) {
                    $this->dispatch('showAlert', [
                        'type' => 'error',
                        'title' => 'خطا در ارسال!',
                        'text' => 'خطا در ارسال به ' . ($resident->resident_full_name ?? 'نامشخص') . ': ' . $e->getMessage(),
                    ]);
                }
            }
            
            $this->dispatch('$refresh');
        }

        $this->isSending = false;
        $this->sendingProgress['current'] = null;

        // نمایش پیام نتیجه
        $message = "{$this->sendingProgress['sent']} پیامک با موفقیت ارسال شد.";
        if ($this->sendingProgress['failed'] > 0) {
            $message .= " {$this->sendingProgress['failed']} پیامک با خطا مواجه شد.";
        }

        $this->dispatch('showAlert', [
            'type' => $this->sendingProgress['failed'] > 0 ? 'warning' : 'success',
            'title' => $this->sendingProgress['failed'] > 0 ? 'توجه!' : 'موفقیت!',
            'text' => $message,
        ]);
        
        // پاک کردن انتخاب‌ها
        $this->selectedResidents = [];
        $this->selectAll = false;
    }

    /**
     * استخراج و جایگزینی متغیرها در الگو
     */
    protected function extractPatternVariables($patternText, $residentData, $residentApiData = null)
    {
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[1])) {
            return [];
        }

        // استفاده از داده‌های API اگر موجود باشد، در غیر این صورت از داده‌های دیتابیس
        $residentDataForVariables = $residentApiData ?? $this->getResidentDataFromDb($residentData);

        $variables = PatternVariable::where('is_active', true)
            ->get()
            ->keyBy('code');

        $result = [];
        $usedIndices = array_unique(array_map('intval', $matches[1]));
        sort($usedIndices);

        foreach ($usedIndices as $index) {
            $code = '{' . $index . '}';
            $variable = $variables->get($code);

            if ($variable) {
                $value = $this->getVariableValue($variable, $residentDataForVariables, null);
                $result[] = $value;
            } else {
                $result[] = '';
            }
        }

        return $result;
    }

    protected function getResidentDataFromDb($residentData)
    {
        return [
            'resident' => [
                'id' => $residentData['id'] ?? $residentData['resident_id'] ?? null,
                'full_name' => $residentData['name'] ?? $residentData['resident_name'] ?? '',
                'name' => $residentData['name'] ?? $residentData['resident_name'] ?? '',
                'phone' => $residentData['phone'] ?? '',
            ],
            'unit' => [
                'id' => $residentData['unit_id'] ?? null,
                'name' => $residentData['unit_name'] ?? '',
            ],
            'room' => [
                'id' => $residentData['room_id'] ?? null,
                'name' => $residentData['room_name'] ?? '',
            ],
            'bed' => [
                'id' => $residentData['bed_id'] ?? null,
                'name' => $residentData['bed_name'] ?? '',
            ],
        ];
    }

    protected function getVariableValue($variable, $residentData, $reportData)
    {
        $field = $variable->table_field;
        $type = $variable->variable_type;

        if ($type === 'user') {
            if (strpos($field, 'unit_') === 0) {
                $key = substr($field, 5);
                return $residentData['unit'][$key] ?? '';
            } elseif (strpos($field, 'room_') === 0) {
                $key = substr($field, 5);
                return $residentData['room'][$key] ?? '';
            } elseif (strpos($field, 'bed_') === 0) {
                $key = substr($field, 4);
                return $residentData['bed'][$key] ?? '';
            } else {
                $value = $residentData['resident'][$field] ?? '';
                
                if (empty($value)) {
                    if ($field === 'full_name' || $field === 'name') {
                        $value = $residentData['resident']['name'] ?? 
                                 $residentData['resident']['full_name'] ?? '';
                    } elseif ($field === 'phone') {
                        $value = $residentData['resident']['phone'] ?? '';
                    }
                }
                
                return $value;
            }
        } elseif ($type === 'report' && $reportData) {
            if (strpos($field, 'category.') === 0) {
                $key = substr($field, 9);
                return $reportData['category_' . $key] ?? '';
            } else {
                return $reportData[$field] ?? '';
            }
        } elseif ($type === 'general') {
            if ($field === 'today') {
                return $this->formatJalaliDate(now()->toDateString());
            }
        }

        return '';
    }

    protected function formatJalaliDate($date)
    {
        if (!$date) {
            return '';
        }

        try {
            if (is_string($date)) {
                $date = \Carbon\Carbon::parse($date);
            }

            if (class_exists(\Morilog\Jalali\Jalalian::class)) {
                return \Morilog\Jalali\Jalalian::fromCarbon($date)->format('Y/m/d');
            }

            return $date->format('Y/m/d');
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * بررسی اینکه آیا اقامت‌گر باید غیرفعال باشد (بر اساس note end_date)
     * اگر تاریخ سررسید اقامت‌گر کوچکتر از تاریخ end_date در note باشد، غیرفعال می‌شود
     */
    public function isResidentDisabled($resident)
    {
        // بررسی وجود notes
        if (!$resident->notes || !is_array($resident->notes)) {
            return ['disabled' => false, 'reason' => null];
        }

        // پیدا کردن note با type="end_date"
        $endDateNote = null;
        foreach ($resident->notes as $note) {
            if (isset($note['note_type']) && $note['note_type'] === 'end_date' && isset($note['note_note'])) {
                $endDateNote = $note;
                break;
            }
        }

        // اگر note end_date پیدا نشد، فعال است
        if (!$endDateNote || empty($endDateNote['note_note'])) {
            return ['disabled' => false, 'reason' => null];
        }

        // دریافت تاریخ سررسید اقامت‌گر
        $contractPaymentDate = $resident->contract_payment_date_jalali;
        if (!$contractPaymentDate || empty(trim($contractPaymentDate))) {
            return ['disabled' => false, 'reason' => null];
        }

        try {
            // تبدیل تاریخ‌ها به عدد برای مقایسه
            $contractDateNumeric = (int) str_replace('/', '', trim($contractPaymentDate));
            $endDateNumeric = (int) str_replace('/', '', trim($endDateNote['note_note']));

            // اگر تاریخ سررسید کوچکتر از تاریخ end_date باشد، غیرفعال است
            if ($contractDateNumeric < $endDateNumeric) {
                return [
                    'disabled' => true,
                    'reason' => "تاریخ سررسید ({$contractPaymentDate}) کوچکتر از تاریخ پایان ثبت شده در نوت ({$endDateNote['note_note']}) است"
                ];
            }

            // اگر مساوی یا بزرگتر باشد، فعال است
            return ['disabled' => false, 'reason' => null];
        } catch (\Exception $e) {
            Log::warning('Error comparing dates in isResidentDisabled', [
                'resident_id' => $resident->id,
                'error' => $e->getMessage()
            ]);
            return ['disabled' => false, 'reason' => null];
        }
    }

    private function getFilteredResidentsQuery()
    {
        // دریافت تاریخ امروز به صورت شمسی
        $todayJalali = null;
        if (class_exists(\Morilog\Jalali\Jalalian::class)) {
            $todayJalali = \Morilog\Jalali\Jalalian::fromCarbon(now())->format('Y/m/d');
        } else {
            $todayJalali = now()->format('Y/m/d');
        }

        // تبدیل تاریخ امروز به عدد برای مقایسه
        $todayJalaliNumeric = (int) str_replace('/', '', $todayJalali);

        $query = Resident::whereNotNull('contract_payment_date_jalali')
            ->where('contract_payment_date_jalali', '!=', '')
            ->whereRaw("CAST(REPLACE(contract_payment_date_jalali, '/', '') AS UNSIGNED) <= ?", [$todayJalaliNumeric]);

        // جستجو در نام، تلفن
        if ($this->search) {
            $query->where(function($q) {
                $q->where('resident_full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('resident_phone', 'like', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    public function render()
    {
        // مرتب‌سازی بر اساس تاریخ سررسید (از امروز به قبل - جدیدترین اول)
        $residents = $this->getFilteredResidentsQuery()
            ->orderByRaw("CAST(REPLACE(contract_payment_date_jalali, '/', '') AS UNSIGNED) DESC")
            ->orderBy('resident_full_name', 'asc')
            ->paginate($this->perPage);

        // محاسبه selectAll بر اساس اینکه آیا همه اقامت‌گران فعال انتخاب شده‌اند
        $allFilteredResidents = $this->getFilteredResidentsQuery()->get();
        $activeResidentIds = [];
        
        foreach ($allFilteredResidents as $resident) {
            $disabledInfo = $this->isResidentDisabled($resident);
            if (!$disabledInfo['disabled']) {
                $activeResidentIds[] = (int)$resident->id;
            }
        }
        
        // اطمینان از اینکه selectedResidents یک array است
        if (!is_array($this->selectedResidents)) {
            $this->selectedResidents = [];
        }
        
        // تبدیل selectedResidents به array از integer
        $currentSelected = array_map('intval', $this->selectedResidents);
        
        // تبدیل activeResidentIds به array از integer
        $activeResidentIds = array_map('intval', $activeResidentIds);
        
        // بررسی اینکه آیا همه اقامت‌گران فعال انتخاب شده‌اند
        $allActiveSelected = !empty($activeResidentIds) && 
                            count($activeResidentIds) === count($currentSelected) && 
                            empty(array_diff($activeResidentIds, $currentSelected));
        
        if ($allActiveSelected && !$this->selectAll) {
            $this->selectAll = true;
        } elseif (!$allActiveSelected && $this->selectAll) {
            $this->selectAll = false;
        }

        return view('livewire.residents.expired-today', [
            'residents' => $residents,
        ]);
    }
}


