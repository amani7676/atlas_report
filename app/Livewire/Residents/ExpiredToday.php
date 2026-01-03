<?php

namespace App\Livewire\Residents;

use App\Models\Resident;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Settings;
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
    public $patternReportWarning = null; // آلارم برای عدم وجود گزارش
    
    // Sending progress
    public $isSending = false;
    public $showProgressModal = false;
    public $isCancelled = false;
    public $sendingProgress = [
        'total' => 0,
        'sent' => 0,
        'failed' => 0,
        'current' => null,
        'current_index' => 0,
        'completed' => false,
        'result_message' => null,
        'errors' => [], // آرایه برای ذخیره جزئیات خطاها
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
        // بررسی اینکه آیا برای الگوی انتخابی گزارش ست شده یا نه
        $this->checkPatternReport();
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
        // فقط الگوهایی که فعال، تایید شده، دارای pattern_code و برایشان گزارش ست شده هستند
        $this->patterns = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code')
            ->whereHas('reports', function ($query) {
                $query->where('report_pattern.is_active', true);
            })
            ->orderBy('title')
            ->get();
    }

    /**
     * بررسی اینکه آیا برای الگوی انتخابی گزارش ست شده یا نه
     */
    public function checkPatternReport()
    {
        $this->patternReportWarning = null;
        
        if (!$this->selectedPattern) {
            return;
        }
        
        $pattern = Pattern::find($this->selectedPattern);
        if (!$pattern) {
            return;
        }
        
        // بررسی اینکه آیا برای این الگو گزارش ست شده یا نه
        $reports = $pattern->reports()->wherePivot('is_active', true)->get();
        
        if ($reports->isEmpty()) {
            $this->patternReportWarning = 'گزارشی برای پیام انتخابی ثبت نشده';
        }
    }

    /**
     * ثبت خودکار گزارش برای اقامت‌گر هنگام ارسال پیامک
     */
    protected function createReportForResident($pattern, $resident)
    {
        try {
            // پیدا کردن اولین گزارش فعال مرتبط با الگو
            $report = $pattern->reports()->wherePivot('is_active', true)->first();
            
            if (!$report) {
                // اگر گزارش ست نشده باشد، چیزی ثبت نمی‌کنیم
                return;
            }
            
            // بررسی اینکه آیا قبلاً این گزارش برای این اقامت‌گر ثبت شده یا نه
            $existingReport = ResidentReport::where('report_id', $report->id)
                ->where('resident_id', $resident->id)
                ->whereDate('created_at', now()->toDateString())
                ->first();
            
            if ($existingReport) {
                // اگر امروز قبلاً ثبت شده، چیزی ثبت نمی‌کنیم
                return;
            }
            
            // ثبت گزارش جدید
            ResidentReport::create([
                'report_id' => $report->id,
                'resident_id' => $resident->id,
                'notes' => 'ایجاد شده به صورت خودکار از ارسال پیامک سررسید',
                'has_been_sent' => true,
                'is_checked' => false,
            ]);
            
            Log::info('ExpiredToday - Report created automatically', [
                'report_id' => $report->id,
                'resident_id' => $resident->id,
                'pattern_id' => $pattern->id,
            ]);
        } catch (\Exception $e) {
            Log::error('ExpiredToday - Error creating report', [
                'resident_id' => $resident->id,
                'pattern_id' => $pattern->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * شروع فرآیند ارسال - فقط مدال را نمایش می‌دهد
     */
    public function startSending()
    {
        // بررسی اینکه آیا می‌توان ارسال کرد
        if (!$this->canSend) {
            return;
        }

        // Reset progress و نمایش مدال - باید قبل از هر کار دیگری باشد
        $this->isSending = true;
        $this->isCancelled = false;
        $this->showProgressModal = true;
        $this->sendingProgress = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
            'current' => 'در حال آماده‌سازی...',
            'current_index' => 0,
            'completed' => false,
            'result_message' => null,
            'errors' => [],
        ];
        
        // Dispatch event برای نمایش مدال و قفل صفحه
        $this->dispatch('show-progress-modal');
    }

    /**
     * ارسال پیامک با الگو
     */
    public function sendPatternSms()
    {
        // اعتبارسنجی
        if (!$this->selectedPattern) {
            return;
        }

        if (empty($this->selectedResidents)) {
            return;
        }

        $pattern = Pattern::find($this->selectedPattern);
        if (!$pattern || !$pattern->pattern_code) {
            return;
        }

        // بررسی اینکه آیا برای این الگو گزارش ست شده یا نه
        $reports = $pattern->reports()->wherePivot('is_active', true)->get();
        if ($reports->isEmpty()) {
            // اگر گزارش ست نشده باشد، فقط آلارم نمایش می‌دهیم و هیچ کاری نمی‌کنیم
            $this->patternReportWarning = 'گزارشی برای پیام انتخابی ثبت نشده';
            $this->isSending = false;
            $this->showProgressModal = false;
            return;
        }

        // دریافت اقامت‌گران انتخاب شده
        $selectedResidents = Resident::whereIn('id', $this->selectedResidents)
            ->whereNotNull('resident_phone')
            ->where('resident_phone', '!=', '')
            ->get();

        if ($selectedResidents->isEmpty()) {
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
            return;
        }

        // دریافت شماره فرستنده و API Key از شماره انتخاب شده (مشابه PatternTest)
        // در ExpiredToday ما شماره فرستنده انتخاب نمی‌کنیم، پس از دیتابیس یا config استفاده می‌کنیم
        $senderNumberObj = null;
        $apiKey = null;
        
        // اول سعی می‌کنیم از SenderNumber استفاده کنیم (اگر در آینده اضافه شد)
        // در حال حاضر از دیتابیس یا config استفاده می‌کنیم
        
        // دریافت API Key از دیتابیس یا config
        $dbConsoleKey = \App\Models\ApiKey::getKeyValue('console_api_key');
        $dbApiKey = \App\Models\ApiKey::getKeyValue('api_key');
        $configConsoleKey = config('services.melipayamak.console_api_key');
        $configApiKey = config('services.melipayamak.api_key');
        
        Log::info('ExpiredToday - API Key sources', [
            'db_console_key_exists' => !empty($dbConsoleKey),
            'db_console_key_length' => $dbConsoleKey ? strlen($dbConsoleKey) : 0,
            'db_console_key_preview' => $dbConsoleKey ? substr($dbConsoleKey, 0, 8) . '...' : 'empty',
            'db_api_key_exists' => !empty($dbApiKey),
            'db_api_key_length' => $dbApiKey ? strlen($dbApiKey) : 0,
            'db_api_key_preview' => $dbApiKey ? substr($dbApiKey, 0, 8) . '...' : 'empty',
            'config_console_key_exists' => !empty($configConsoleKey),
            'config_api_key_exists' => !empty($configApiKey),
        ]);
        
        $apiKey = $dbConsoleKey
            ?: $dbApiKey
            ?: $configConsoleKey
            ?: $configApiKey;

        $melipayamakService = new MelipayamakService();
        $residentService = new ResidentService();
        
        // دریافت تنظیمات تاخیر از دیتابیس
        $settings = Settings::getSettings();
        $delayBeforeStart = ($settings->sms_delay_before_start ?? 2) * 1000000; // تبدیل ثانیه به میکروثانیه
        $delayBetweenMessages = ($settings->sms_delay_between_messages ?? 200) * 1000; // تبدیل میلی‌ثانیه به میکروثانیه
        
        // Reset progress و نمایش مدال - باید قبل از هر کار دیگری باشد
        $this->isSending = true;
        $this->isCancelled = false;
        $this->showProgressModal = true;
        $this->sendingProgress = [
            'total' => count($activeResidents),
            'sent' => 0,
            'failed' => 0,
            'current' => 'در حال آماده‌سازی...',
            'current_index' => 0,
            'errors' => [], // آرایه برای ذخیره جزئیات خطاها
        ];
        
        // Dispatch event برای نمایش مدال و قفل صفحه
        $this->dispatch('show-progress-modal');
        
        // اطمینان از render شدن مدال
        $this->dispatch('$refresh');
        
        // تاخیر قبل از شروع ارسال (از تنظیمات) - با به‌روزرسانی مدال
        if ($delayBeforeStart > 0) {
            $steps = 10; // 10 مرحله برای به‌روزرسانی مدال
            $stepDelay = $delayBeforeStart / $steps;
            for ($i = 0; $i < $steps; $i++) {
                if ($this->isCancelled) {
                    $this->isSending = false;
                    $this->showProgressModal = false;
                    $this->dispatch('hide-progress-modal');
                    return;
                }
                $remainingSeconds = ceil(($delayBeforeStart - ($i * $stepDelay)) / 1000000);
                $this->sendingProgress['current'] = 'در حال آماده‌سازی... (' . $remainingSeconds . ' ثانیه باقی مانده)';
                usleep($stepDelay);
            }
        }
        
        // به‌روزرسانی وضعیت - شروع واقعی ارسال
        $this->sendingProgress['current'] = 'شروع ارسال...';

        // ارسال به صورت تکی در حلقه با تاخیر قابل تنظیم (فقط اقامت‌گران فعال)
        $index = 0;
        foreach ($activeResidents as $resident) {
            // بررسی لغو شدن
            if ($this->isCancelled) {
                break;
            }

            $index++;
            $this->sendingProgress['current_index'] = $index;
            
            if (empty($resident->resident_phone)) {
                $this->sendingProgress['failed']++;
                // تاخیر بین پیام‌ها (از تنظیمات)
                if ($delayBetweenMessages > 0 && $index < count($activeResidents)) {
                    usleep($delayBetweenMessages);
                }
                continue;
            }

            $this->sendingProgress['current'] = $resident->resident_full_name ?? 'بدون نام';
            
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
                    'full_name' => $resident->resident_full_name, // اضافه کردن full_name برای متغیر {0}
                    'phone' => $resident->resident_phone,
                    'unit_id' => $resident->unit_id ?? null,
                    'unit_name' => $resident->unit_name ?? '',
                    'room_id' => $resident->room_id ?? null,
                    'room_name' => $resident->room_name ?? '',
                    'bed_id' => $resident->bed_id ?? null,
                    'bed_name' => $resident->bed_name ?? '',
                    'contract_payment_date_jalali' => $resident->contract_payment_date_jalali ?? '',
                    'payment_date_jalali' => $resident->contract_payment_date_jalali ?? '',
                ];
                
                // لاگ داده‌های resident برای دیباگ
                Log::info('ExpiredToday - Resident data prepared', [
                    'resident_id' => $resident->id,
                    'resident_full_name' => $resident->resident_full_name,
                    'contract_payment_date_jalali' => $resident->contract_payment_date_jalali,
                    'resident_data' => $residentData,
                ]);

                // استخراج متغیرها از متن الگو
                $variables = $this->extractPatternVariables($pattern->text, $residentData, $residentApiData);

                // لاگ متغیرها برای دیباگ
                Log::info('ExpiredToday - Pattern variables extracted', [
                    'pattern_id' => $pattern->id,
                    'pattern_text' => $pattern->text,
                    'pattern_code' => $pattern->pattern_code,
                    'variables' => $variables,
                    'variables_count' => count($variables),
                    'resident_name' => $resident->resident_full_name,
                    'resident_phone' => $resident->resident_phone,
                ]);

                // اطمینان از اینکه pattern_code عدد است
                $bodyId = (int)$pattern->pattern_code;
                
                // اطمینان از اینکه variables یک آرایه است
                if (!is_array($variables)) {
                    $variables = [];
                }
                
                // بررسی اینکه آیا متغیرها خالی هستند
                if (empty($variables) || (count($variables) > 0 && empty(array_filter($variables)))) {
                    Log::warning('ExpiredToday - Variables are empty or all empty', [
                        'pattern_id' => $pattern->id,
                        'pattern_text' => $pattern->text,
                        'variables' => $variables,
                    ]);
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

                // ارسال پیامک با الگو - استفاده از sendByBaseNumber (SOAP API) مشابه PatternTest
                // این متد از username/password استفاده می‌کند و معمولاً قابل اعتمادتر است
                Log::info('ExpiredToday - Using sendByBaseNumber (SOAP API) like PatternTest', [
                    'bodyId' => $bodyId,
                    'variables' => $variables,
                    'variables_count' => count($variables),
                    'phone' => $resident->resident_phone,
                    'api_key_provided' => !empty($apiKey),
                ]);
                
                // استفاده از sendByBaseNumber (SOAP API) مشابه PatternTest
                // این متد از username/password از config استفاده می‌کند
                $result = $melipayamakService->sendByBaseNumber(
                    $resident->resident_phone,
                    $bodyId,
                    $variables,
                    null, // شماره فرستنده (null = از config استفاده می‌شود)
                    $apiKey // API Key (اختیاری - برای SOAP API استفاده نمی‌شود)
                );
                
                // لاگ نتیجه ارسال
                Log::info('ExpiredToday - sendByBaseNumber2 result', [
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'No message',
                    'response_code' => $result['response_code'] ?? null,
                    'rec_id' => $result['rec_id'] ?? null,
                    'status' => $result['status'] ?? null,
                    'http_status_code' => $result['http_status_code'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                ]);

                if ($result['success']) {
                    $smsMessageResident->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'response_code' => $result['response_code'] ?? null,
                        'error_message' => null,
                    ]);
                    $this->sendingProgress['sent']++;
                    
                    // ثبت خودکار گزارش برای اقامت‌گر (اگر برای الگو گزارش ست شده باشد)
                    $this->createReportForResident($pattern, $resident);
                } else {
                    $errorMessage = $result['message'] ?? 'خطای نامشخص';
                    $responseCode = $result['response_code'] ?? 'نامشخص';
                    $rawResponse = $result['raw_response'] ?? null;
                    
                    // ذخیره جزئیات خطا
                    $this->sendingProgress['errors'][] = [
                        'resident_name' => $resident->resident_full_name ?? 'نامشخص',
                        'phone' => $resident->resident_phone ?? 'نامشخص',
                        'error_message' => $errorMessage,
                        'response_code' => $responseCode,
                        'raw_response' => $rawResponse,
                        'http_status_code' => $result['http_status_code'] ?? null,
                        'status' => $result['status'] ?? null,
                    ];
                    
                    $smsMessageResident->update([
                        'status' => 'failed',
                        'error_message' => $errorMessage,
                        'response_code' => $responseCode,
                    ]);
                    $this->sendingProgress['failed']++;
                }
            } catch (\Exception $e) {
                Log::error('Error sending SMS to resident', [
                    'resident_id' => $resident->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // ذخیره جزئیات خطای exception
                $this->sendingProgress['errors'][] = [
                    'resident_name' => $resident->resident_full_name ?? 'نامشخص',
                    'phone' => $resident->resident_phone ?? 'نامشخص',
                    'error_message' => 'خطا در ارسال: ' . $e->getMessage(),
                    'response_code' => 'exception',
                    'raw_response' => $e->getMessage(),
                    'http_status_code' => null,
                    'status' => null,
                ];
                
                $this->sendingProgress['failed']++;
                
            }
            
            // تاخیر بین پیام‌ها (از تنظیمات) - فقط اگر آخرین پیام نباشد
            if (!$this->isCancelled && $index < count($activeResidents) && $delayBetweenMessages > 0) {
                usleep($delayBetweenMessages);
            }
        }

        // اتمام ارسال - به‌روزرسانی نهایی و نمایش نتیجه
        $this->isSending = false;
        $this->sendingProgress['current'] = null;
        $this->sendingProgress['completed'] = true;
        
        // ساخت پیام نتیجه
        if (!$this->isCancelled) {
            $message = "{$this->sendingProgress['sent']} پیامک با موفقیت ارسال شد.";
            if ($this->sendingProgress['failed'] > 0) {
                $message .= " {$this->sendingProgress['failed']} پیامک با خطا مواجه شد.";
            }
            $this->sendingProgress['result_message'] = $message;
            
            // پاک کردن انتخاب‌ها
            $this->selectedResidents = [];
            $this->selectAll = false;
        } else {
            $this->sendingProgress['result_message'] = 'ارسال لغو شد. ' . $this->sendingProgress['sent'] . ' پیامک ارسال شده بود.';
        }
        
        // Reset لغو شدن
        $this->isCancelled = false;
    }

    /**
     * لغو ارسال پیام‌ها
     */
    public function cancelSending()
    {
        $this->isCancelled = true;
        $this->isSending = false;
        $this->sendingProgress['current'] = null;
        $this->sendingProgress['completed'] = true;
        $this->sendingProgress['result_message'] = 'ارسال لغو شد. ' . $this->sendingProgress['sent'] . ' پیامک ارسال شده بود.';
    }

    /**
     * بستن مدال پیشرفت
     */
    public function closeProgressModal()
    {
        $this->showProgressModal = false;
        $this->isSending = false;
        $this->sendingProgress = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
            'current' => null,
            'current_index' => 0,
            'completed' => false,
            'result_message' => null,
            'errors' => [],
        ];
        $this->selectedResidents = [];
        $this->selectAll = false;
        $this->dispatch('hide-progress-modal');
    }

    /**
     * استخراج و جایگزینی متغیرها در الگو
     */
    protected function extractPatternVariables($patternText, $residentData, $residentApiData = null)
    {
        // پیدا کردن تمام متغیرها در الگو (مثل {0}, {1}, {2})
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[1])) {
            return []; // اگر متغیری وجود نداشت
        }

        // استفاده از داده‌های API اگر موجود باشد، در غیر این صورت از داده‌های دیتابیس
        $residentDataForVariables = $residentApiData ?? $this->getResidentDataFromDb($residentData);

        // بارگذاری متغیرها از دیتابیس
        $variables = PatternVariable::where('is_active', true)
            ->get()
            ->keyBy('code'); // کلید بر اساس کد (مثل {0}, {1})

        $result = [];
        $usedIndices = array_unique(array_map('intval', $matches[1]));
        sort($usedIndices); // مرتب‌سازی بر اساس ترتیب در الگو

        Log::debug('ExpiredToday - Extracting pattern variables', [
            'pattern_text' => $patternText,
            'used_indices' => $usedIndices,
            'resident_id' => $residentData['id'] ?? $residentData['resident_id'] ?? null,
        ]);

        Log::info('ExpiredToday - Pattern variables from database', [
            'total_variables' => $variables->count(),
            'variable_codes' => $variables->keys()->toArray(),
            'used_indices' => $usedIndices,
        ]);

        foreach ($usedIndices as $index) {
            $code = '{' . $index . '}';
            $variable = $variables->get($code);

            if ($variable) {
                $value = $this->getVariableValue($variable, $residentDataForVariables, null);
                
                // اطمینان از اینکه value یک رشته است
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                
                // اگر مقدار خالی است، لاگ می‌کنیم
                if (empty(trim($value))) {
                    Log::warning('ExpiredToday - Variable value is empty', [
                        'code' => $code,
                        'index' => $index,
                        'table_field' => $variable->table_field,
                        'variable_type' => $variable->variable_type,
                    ]);
                    $value = ''; // مقدار خالی - API باید آن را قبول کند
                }
                
                Log::info('ExpiredToday - Variable extracted successfully', [
                    'code' => $code,
                    'index' => $index,
                    'table_field' => $variable->table_field,
                    'variable_type' => $variable->variable_type,
                    'value' => $value,
                    'value_length' => strlen($value),
                ]);
                $result[] = $value;
            } else {
                // اگر متغیر در دیتابیس پیدا نشد، مقدار خالی
                Log::error('ExpiredToday - Variable not found in database', [
                    'code' => $code,
                    'index' => $index,
                    'pattern_text' => $patternText,
                    'available_variables' => $variables->keys()->toArray(),
                ]);
                $result[] = ''; // مقدار خالی برای متغیرهای پیدا نشده
            }
        }

        Log::debug('ExpiredToday - Pattern variables extracted', [
            'variables' => $result,
            'variables_count' => count($result),
        ]);

        return $result;
    }

    protected function getResidentDataFromDb($residentData)
    {
        // ساخت ساختار داده مشابه PatternManual
        $result = [
            'resident' => [
                'id' => $residentData['id'] ?? $residentData['resident_id'] ?? null,
                'full_name' => $residentData['full_name'] ?? $residentData['name'] ?? $residentData['resident_name'] ?? '',
                'name' => $residentData['name'] ?? $residentData['resident_name'] ?? $residentData['full_name'] ?? '',
                'phone' => $residentData['phone'] ?? '',
                'contract_payment_date_jalali' => $residentData['contract_payment_date_jalali'] ?? $residentData['payment_date_jalali'] ?? '',
                'payment_date_jalali' => $residentData['contract_payment_date_jalali'] ?? $residentData['payment_date_jalali'] ?? '',
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
        
        Log::debug('ExpiredToday - Using fallback resident data from DB', [
            'fallback_data' => $result,
            'input_data' => $residentData,
        ]);
        
        Log::info('ExpiredToday - getResidentDataFromDb result', [
            'full_name' => $result['resident']['full_name'],
            'contract_payment_date_jalali' => $result['resident']['contract_payment_date_jalali'],
            'payment_date_jalali' => $result['resident']['payment_date_jalali'],
        ]);
        
        return $result;
    }

    protected function getVariableValue($variable, $residentData, $reportData)
    {
        $field = $variable->table_field ?? '';
        $type = $variable->variable_type ?? 'user';
        
        Log::debug('ExpiredToday - Getting variable value', [
            'field' => $field,
            'type' => $type,
            'variable_id' => $variable->id ?? null,
        ]);

        if ($type === 'user') {
            // فیلدهای کاربر
            if (strpos($field, 'unit_') === 0) {
                $key = substr($field, 5); // حذف 'unit_' از ابتدا
                $value = $residentData['unit'][$key] ?? '';
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                Log::debug('ExpiredToday - Getting unit field', [
                    'field' => $field,
                    'key' => $key,
                    'value' => $value,
                ]);
                return $value;
            } elseif (strpos($field, 'room_') === 0) {
                $key = substr($field, 5); // حذف 'room_' از ابتدا
                $value = $residentData['room'][$key] ?? '';
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                Log::debug('ExpiredToday - Getting room field', [
                    'field' => $field,
                    'key' => $key,
                    'value' => $value,
                ]);
                return $value;
            } elseif (strpos($field, 'bed_') === 0) {
                $key = substr($field, 4); // حذف 'bed_' از ابتدا
                $value = $residentData['bed'][$key] ?? '';
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                Log::debug('ExpiredToday - Getting bed field', [
                    'field' => $field,
                    'key' => $key,
                    'value' => $value,
                ]);
                return $value;
            } else {
                // فیلدهای مستقیم resident (مثل full_name, phone, name, contract_payment_date_jalali, etc.)
                // بررسی چند حالت مختلف برای سازگاری
                $value = $residentData['resident'][$field] ?? '';
                
                // اگر مقدار پیدا نشد، سعی می‌کنیم نام‌های جایگزین را بررسی کنیم
                if (empty($value)) {
                    if ($field === 'full_name' || $field === 'name') {
                        $value = $residentData['resident']['name'] ?? 
                                 $residentData['resident']['full_name'] ?? 
                                 ($residentData['resident']['id'] ?? '');
                    } elseif ($field === 'phone') {
                        $value = $residentData['resident']['phone'] ?? '';
                    } elseif ($field === 'contract_payment_date_jalali' || $field === 'payment_date_jalali') {
                        $value = $residentData['resident']['contract_payment_date_jalali'] ?? 
                                 $residentData['resident']['payment_date_jalali'] ?? '';
                    } elseif ($field === 'national_id' || $field === 'national_code') {
                        $value = $residentData['resident']['national_id'] ?? 
                                 $residentData['resident']['national_code'] ?? '';
                    }
                }
                
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                Log::debug('ExpiredToday - Getting resident field', [
                    'field' => $field,
                    'value' => $value,
                    'available_fields' => array_keys($residentData['resident'] ?? []),
                ]);
                return $value;
            }
        } elseif ($type === 'report' && $reportData) {
            // فیلدهای گزارش
            if (strpos($field, 'category.') === 0) {
                $key = substr($field, 9); // حذف 'category.' از ابتدا
                $value = $reportData['category_' . $key] ?? '';
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                Log::debug('ExpiredToday - Getting category field', [
                    'field' => $field,
                    'key' => $key,
                    'value' => $value,
                ]);
                return $value;
            } else {
                $value = $reportData[$field] ?? '';
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                Log::debug('ExpiredToday - Getting report field', [
                    'field' => $field,
                    'value' => $value,
                ]);
                return $value;
            }
        } elseif ($type === 'general') {
            if ($field === 'today') {
                $value = $this->formatJalaliDate(now()->toDateString());
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                Log::debug('ExpiredToday - Getting general today field', [
                    'field' => $field,
                    'value' => $value,
                ]);
                return $value;
            }
        }

        Log::warning('ExpiredToday - Variable value not found', [
            'field' => $field,
            'type' => $type,
        ]);

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


