<?php

namespace App\Livewire\Residents;

use App\Models\Resident;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
use App\Models\SenderNumber;
use App\Models\Settings;
use App\Services\MelipayamakService;
use App\Services\ResidentService;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;

class GroupSms extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // فیلترها
    public $filters = [
        'unit_name' => null,
        'room_name' => null,
        'bed_name' => null,
        'resident_full_name' => null,
        'resident_phone' => null,
        'resident_document' => null, // null, true, false
        'resident_form' => null, // null, true, false
        'contract_state' => null, // active, inactive, etc
        'payment_overdue_days' => null, // تعداد روزهای گذشته از سررسید
        'resident_status' => null, // active, exit (deleted)
        'notes_type' => null, // payment, end_date, exit, demand, other
        'has_debt' => null, // null, true, false
    ];

    public $selectedResidents = [];
    public $selectAll = false;
    public $perPage = 20;
    public $showFilters = true;

    // لیست واحدها، اتاق‌ها و تخت‌ها برای dropdown
    public $unitsList = [];
    public $roomsList = [];
    public $bedsList = [];

    // Pattern SMS properties
    public $selectedPattern = null;
    public $patterns = [];
    public $selectedSenderNumberId = null;
    public $availableSenderNumbers = [];
    
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
    ];
    
    // نتایج ارسال برای نمایش در مدال
    public $sendResults = [];

    public function mount()
    {
        $this->loadFilterOptions();
        $this->loadPatterns();
        $this->loadSenderNumbers();
        
        // اگر از صفحه expired-today آمده، افراد انتخاب شده را از session بخوان
        if (session()->has('selected_residents_for_sms')) {
            $this->selectedResidents = session('selected_residents_for_sms');
            session()->forget('selected_residents_for_sms');
        }
    }

    public function loadFilterOptions()
    {
        // بارگذاری لیست واحدها
        $this->unitsList = Resident::whereNotNull('unit_name')
            ->distinct()
            ->orderBy('unit_name', 'asc')
            ->pluck('unit_name')
            ->toArray();

        // بارگذاری لیست اتاق‌ها
        $this->roomsList = Resident::whereNotNull('room_name')
            ->distinct()
            ->orderBy('room_name', 'asc')
            ->pluck('room_name')
            ->toArray();

        // بارگذاری لیست تخت‌ها
        $this->bedsList = Resident::whereNotNull('bed_name')
            ->distinct()
            ->orderBy('bed_name', 'asc')
            ->pluck('bed_name')
            ->toArray();
    }

    public function updatedFilters()
    {
        $this->resetPage();
        $this->selectedResidents = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll()
    {
        // این متد برای همگام‌سازی با تغییرات دستی selectAll استفاده می‌شود
        // اما ما از toggleSelectAll استفاده می‌کنیم، پس این متد خالی می‌ماند
    }

    public function resetFilters()
    {
        $this->filters = [
            'unit_name' => null,
            'room_name' => null,
            'bed_name' => null,
            'resident_full_name' => null,
            'resident_phone' => null,
            'resident_document' => null,
            'resident_form' => null,
            'contract_state' => null,
            'payment_overdue_days' => null,
            'resident_status' => null,
            'notes_type' => null,
            'has_debt' => null,
        ];
        $this->resetPage();
        $this->selectedResidents = [];
        $this->selectAll = false;
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedResidents = [];
            $this->selectAll = false;
        } else {
            $residents = $this->getFilteredResidentsQuery()->pluck('id')->toArray();
            $this->selectedResidents = $residents;
            $this->selectAll = true;
        }
    }

    public function toggleSelectResident($residentId)
    {
        if (in_array($residentId, $this->selectedResidents)) {
            $this->selectedResidents = array_diff($this->selectedResidents, [$residentId]);
        } else {
            $this->selectedResidents[] = $residentId;
        }
        $this->selectAll = false;
    }

    private function getFilteredResidentsQuery()
    {
        $query = Resident::query();

        // فیلتر واحد
        if (!empty($this->filters['unit_name'])) {
            $query->where('unit_name', $this->filters['unit_name']);
        }

        // فیلتر اتاق
        if (!empty($this->filters['room_name'])) {
            $query->where('room_name', $this->filters['room_name']);
        }

        // فیلتر تخت
        if (!empty($this->filters['bed_name'])) {
            $query->where('bed_name', $this->filters['bed_name']);
        }

        // فیلتر نام
        if (!empty($this->filters['resident_full_name'])) {
            $query->where('resident_full_name', 'like', '%' . $this->filters['resident_full_name'] . '%');
        }

        // فیلتر تلفن
        if (!empty($this->filters['resident_phone'])) {
            $query->where('resident_phone', 'like', '%' . $this->filters['resident_phone'] . '%');
        }

        // فیلتر مدرک
        if ($this->filters['resident_document'] !== null && $this->filters['resident_document'] !== '') {
            $query->where('resident_document', $this->filters['resident_document'] == '1' || $this->filters['resident_document'] === true);
        }

        // فیلتر فرم
        if ($this->filters['resident_form'] !== null && $this->filters['resident_form'] !== '') {
            $query->where('resident_form', $this->filters['resident_form'] == '1' || $this->filters['resident_form'] === true);
        }

        // فیلتر وضعیت قرارداد
        if (!empty($this->filters['contract_state'])) {
            $query->where('contract_state', $this->filters['contract_state']);
        }

        // فیلتر گذشته از سررسید
        // اگر کاربر 7 روز وارد کند، باید اقامت‌گرانی را نشان دهد که تاریخ پرداختشان 7 روز قبل از امروز یا قبل‌تر است
        if (!empty($this->filters['payment_overdue_days']) && is_numeric($this->filters['payment_overdue_days'])) {
            $days = (int)$this->filters['payment_overdue_days'];
            $today = now();
            $targetDate = $today->copy()->subDays($days);
            $targetDateJalali = Jalalian::fromCarbon($targetDate);
            $targetDateStr = $targetDateJalali->format('Y/m/d');
            
            // اقامت‌گرانی که تاریخ پرداختشان قبل یا برابر targetDateStr است (یعنی days روز از سررسیدشان گذشته)
            $query->whereNotNull('contract_payment_date_jalali')
                ->where('contract_payment_date_jalali', '!=', '')
                ->where('contract_payment_date_jalali', '<=', $targetDateStr);
        }

        // فیلتر وضعیت کاربر (active/exit)
        if (!empty($this->filters['resident_status'])) {
            if ($this->filters['resident_status'] === 'active') {
                $query->whereNull('resident_deleted_at');
            } elseif ($this->filters['resident_status'] === 'exit') {
                $query->whereNotNull('resident_deleted_at');
            }
        }

        // فیلتر نوت‌ها بر اساس type
        // notes یک JSON array است که هر آیتم آن یک object با field type دارد
        if (!empty($this->filters['notes_type'])) {
            $notesType = $this->filters['notes_type'];
            // استفاده از JSON_SEARCH برای جستجو در array
            $query->whereRaw('JSON_SEARCH(notes, "one", ?, NULL, "$[*].type") IS NOT NULL', [$notesType]);
        }

        // فیلتر بدهی‌ها - نیاز به بررسی بیشتر داریم، فعلاً placeholder
        // TODO: پیاده‌سازی فیلتر بدهی بر اساس منطق کسب‌وکار

        return $query;
    }

    public function loadPatterns()
    {
        // فقط الگوهایی که تایید شده، فعال، دارای pattern_code و برایشان گزارش ست شده هستند
        $this->patterns = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code')
            ->whereHas('reports', function ($query) {
                $query->where('report_pattern.is_active', true);
            })
            ->orderBy('title')
            ->get();
    }

    public function loadSenderNumbers()
    {
        $this->availableSenderNumbers = SenderNumber::getActivePatternNumbers();
        
        if ($this->availableSenderNumbers->count() > 0 && !$this->selectedSenderNumberId) {
            $this->selectedSenderNumberId = $this->availableSenderNumbers->first()->id;
        }
    }

    public function sendSms()
    {
        // اگر الگو انتخاب شده باشد، از روش الگویی استفاده می‌کنیم
        if ($this->selectedPattern) {
            return $this->sendPatternSms();
        }

        // روش قدیمی: هدایت به صفحه ارسال پیامک
        if (empty($this->selectedResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار',
                'text' => 'لطفا حداقل یک اقامت‌گر را انتخاب کنید یا یک الگو را انتخاب کنید.'
            ]);
            return;
        }

        // ذخیره ID های انتخاب شده در session برای استفاده در صفحه ارسال پیامک
        session(['group_sms_selected_residents' => $this->selectedResidents]);
        
        // هدایت به صفحه ارسال پیامک
        return redirect()->route('sms.index');
    }

    /**
     * شروع فرآیند ارسال - فقط مدال را نمایش می‌دهد
     */
    public function startSending()
    {
        // بررسی اینکه آیا می‌توان ارسال کرد
        if (!$this->selectedPattern || empty($this->selectedResidents)) {
            return;
        }

        // بلافاصله نمایش مدال و قفل صفحه - قبل از هر کار دیگری
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
        ];
        $this->sendResults = [];
        
        // Dispatch event برای نمایش مدال و قفل صفحه - بلافاصله
        $this->dispatch('show-progress-modal');
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

        // بررسی اینکه حداقل یک اقامت‌گر انتخاب شده باشد
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
            // اگر مدال باز است، آن را ببند
            if ($this->showProgressModal) {
                $this->closeProgressModal();
            }
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'هیچ اقامت‌گری با شماره تلفن در لیست انتخاب شده یافت نشد.'
            ]);
            return;
        }

        // به‌روزرسانی تعداد کل در مدال (مدال باید قبلاً از startSending باز شده باشد)
        if ($this->showProgressModal) {
            $this->sendingProgress['total'] = $selectedResidents->count();
        }

        // دریافت شماره فرستنده و API Key از شماره انتخاب شده (مشابه ExpiredToday)
        $senderNumberObj = null;
        $apiKey = null;
        if ($this->selectedSenderNumberId) {
            $senderNumberObj = SenderNumber::find($this->selectedSenderNumberId);
            if ($senderNumberObj && !empty($senderNumberObj->api_key)) {
                $apiKey = $senderNumberObj->api_key;
            }
        }
        
        // دریافت API Key از دیتابیس یا config (مشابه ExpiredToday)
        $dbConsoleKey = \App\Models\ApiKey::getKeyValue('console_api_key');
        $dbApiKey = \App\Models\ApiKey::getKeyValue('api_key');
        $configConsoleKey = config('services.melipayamak.console_api_key');
        $configApiKey = config('services.melipayamak.api_key');
        
        Log::info('GroupSms - API Key sources', [
            'db_console_key_exists' => !empty($dbConsoleKey),
            'db_console_key_length' => $dbConsoleKey ? strlen($dbConsoleKey) : 0,
            'db_console_key_preview' => $dbConsoleKey ? substr($dbConsoleKey, 0, 8) . '...' : 'empty',
            'db_api_key_exists' => !empty($dbApiKey),
            'db_api_key_length' => $dbApiKey ? strlen($dbApiKey) : 0,
            'db_api_key_preview' => $dbApiKey ? substr($dbApiKey, 0, 8) . '...' : 'empty',
            'config_console_key_exists' => !empty($configConsoleKey),
            'config_api_key_exists' => !empty($configApiKey),
            'sender_number_id' => $this->selectedSenderNumberId,
            'api_key_from_sender' => !empty($senderNumberObj?->api_key),
        ]);
        
        $apiKey = $dbConsoleKey
            ?: $dbApiKey
            ?: $configConsoleKey
            ?: $configApiKey;
        
        // توجه: برای SOAP API از username/password استفاده می‌شود که در MelipayamakService تنظیم شده
        // API Key برای SOAP API استفاده نمی‌شود اما برای سازگاری نگه داشته شده

        $melipayamakService = new MelipayamakService();
        $residentService = new ResidentService();
        
        // دریافت تنظیمات تاخیر از دیتابیس
        $settings = Settings::getSettings();
        $delayBeforeStart = ($settings->sms_delay_before_start ?? 2) * 1000000; // تبدیل ثانیه به میکروثانیه
        $delayBetweenMessages = ($settings->sms_delay_between_messages ?? 200) * 1000; // تبدیل میلی‌ثانیه به میکروثانیه
        
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

        // ارسال به صورت تکی در حلقه با تاخیر قابل تنظیم
        $index = 0;
        $this->sendResults = []; // پاک کردن نتایج قبلی
        foreach ($selectedResidents as $resident) {
            // بررسی لغو شدن
            if ($this->isCancelled) {
                break;
            }

            $index++;
            $this->sendingProgress['current_index'] = $index;
            
            if (empty($resident->resident_phone)) {
                $this->sendingProgress['failed']++;
                // تاخیر بین پیام‌ها (از تنظیمات)
                if ($delayBetweenMessages > 0 && $index < count($selectedResidents)) {
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
                        Log::error('Error getting resident data from API', [
                            'resident_id' => $resident->resident_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // ساخت داده‌های resident برای استخراج متغیرها از تمام فیلدهای دیتابیس
                $residentData = $resident->toArray(); // استفاده از تمام فیلدهای دیتابیس

                // استخراج متغیرها از متن الگو
                $variables = $this->extractPatternVariables($pattern->text, $residentData, $residentApiData);

                // لاگ اطلاعات برای دیباگ
                Log::info('GroupSms - Sending pattern SMS', [
                    'resident_id' => $resident->id,
                    'resident_name' => $resident->resident_full_name,
                    'phone' => $resident->resident_phone,
                    'pattern_id' => $pattern->id,
                    'pattern_code' => $pattern->pattern_code,
                    'pattern_code_type' => gettype($pattern->pattern_code),
                    'variables_count' => count($variables),
                    'variables' => $variables,
                    'sender_number' => $senderNumberObj ? $senderNumberObj->number : null,
                    'has_api_key' => !empty($apiKey),
                ]);

                // اطمینان از اینکه pattern_code عدد است
                $bodyId = (int)$pattern->pattern_code;
                
                // اطمینان از اینکه variables یک آرایه است
                if (!is_array($variables)) {
                    Log::error('GroupSms - Variables is not an array', [
                        'variables_type' => gettype($variables),
                        'variables_value' => $variables,
                    ]);
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

                // ارسال پیامک با الگو - استفاده از sendByBaseNumber (SOAP API) مشابه ExpiredToday و PatternTest
                // این متد از username/password استفاده می‌کند و معمولاً قابل اعتمادتر است
                Log::info('GroupSms - Using sendByBaseNumber (SOAP API) like ExpiredToday and PatternTest', [
                    'bodyId' => $bodyId,
                    'variables' => $variables,
                    'variables_count' => count($variables),
                    'phone' => $resident->resident_phone,
                    'sender_number' => $senderNumberObj ? $senderNumberObj->number : null,
                    'api_key_provided' => !empty($apiKey),
                ]);
                
                // استفاده از sendByBaseNumber (SOAP API) مشابه ExpiredToday و PatternTest
                // این متد از username/password از config استفاده می‌کند
                $result = $melipayamakService->sendByBaseNumber(
                    $resident->resident_phone,
                    $bodyId,
                    $variables,
                    $senderNumberObj ? $senderNumberObj->number : null, // شماره فرستنده
                    $apiKey // API Key (اختیاری - برای SOAP API استفاده نمی‌شود)
                );
                
                // لاگ نتیجه ارسال
                Log::info('GroupSms - sendByBaseNumber result', [
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'No message',
                    'response_code' => $result['response_code'] ?? null,
                    'rec_id' => $result['rec_id'] ?? null,
                    'status' => $result['status'] ?? null,
                    'http_status_code' => $result['http_status_code'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                ]);

                // لاگ نتیجه ارسال
                Log::info('GroupSms - SMS send result', [
                    'resident_id' => $resident->id,
                    'phone' => $resident->resident_phone,
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'Unknown error',
                    'response_code' => $result['response_code'] ?? null,
                    'rec_id' => $result['rec_id'] ?? null,
                ]);

                // ذخیره نتیجه برای نمایش در مدال
                $this->sendResults[] = [
                    'resident_name' => $resident->resident_full_name ?? 'نامشخص',
                    'phone' => $resident->resident_phone,
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'بدون پیام',
                    'response_code' => $result['response_code'] ?? null,
                    'rec_id' => $result['rec_id'] ?? null,
                    'api_response' => $result['api_response'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                    'full_result' => $result, // تمام اطلاعات نتیجه
                ];

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
                        'api_response' => $result['api_response'] ?? null,
                        'raw_response' => $result['raw_response'] ?? null,
                    ]);
                    $this->sendingProgress['failed']++;
                    
                    // نمایش خطا برای اولین خطا
                    if ($this->sendingProgress['failed'] === 1) {
                        // اگر خطا مربوط به API Key است، پیام واضح‌تری نمایش می‌دهیم
                        if (stripos($errorMessage, 'کلید کنسول') !== false || 
                            stripos($errorMessage, 'console') !== false ||
                            stripos($errorMessage, 'api key') !== false) {
                            $errorMessage = 'API Key کنسول معتبر نیست. لطفاً:\n1. وارد پنل console.melipayamak.com شوید\n2. API Key جدید دریافت کنید\n3. آن را در فایل .env به عنوان MELIPAYAMAK_CONSOLE_API_KEY تنظیم کنید\n\nیا افزونه SOAP را در PHP فعال کنید تا از SOAP API استفاده شود.';
                        }
                        
                        $this->dispatch('showAlert', [
                            'type' => 'error',
                            'title' => 'خطا در ارسال!',
                            'text' => 'خطا در ارسال به ' . ($resident->resident_full_name ?? 'نامشخص') . ': ' . $errorMessage,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending pattern SMS to resident', [
                    'resident_id' => $resident->id,
                    'phone' => $resident->resident_phone ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // ذخیره خطا برای نمایش در مدال
                $this->sendResults[] = [
                    'resident_name' => $resident->resident_full_name ?? 'نامشخص',
                    'phone' => $resident->resident_phone ?? 'نامشخص',
                    'success' => false,
                    'message' => 'خطا: ' . $e->getMessage(),
                    'response_code' => null,
                    'rec_id' => null,
                    'api_response' => null,
                    'raw_response' => $e->getTraceAsString(),
                    'full_result' => [
                        'success' => false,
                        'message' => $e->getMessage(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ],
                ];
                
                $this->sendingProgress['failed']++;
                
                // نمایش خطا برای اولین خطا
                if ($this->sendingProgress['failed'] === 1) {
                    $this->dispatch('showAlert', [
                        'type' => 'error',
                        'title' => 'خطا در ارسال!',
                        'text' => 'خطا در ارسال به ' . ($resident->resident_full_name ?? 'نامشخص') . ': ' . $e->getMessage(),
                    ]);
                }
            }
            
            // تاخیر بین پیام‌ها (از تنظیمات) - فقط اگر آخرین پیام نباشد
            if (!$this->isCancelled && $index < count($selectedResidents) && $delayBetweenMessages > 0) {
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
        $this->showProgressModal = false;
        $this->sendingProgress['current'] = null;
        $this->dispatch('hide-progress-modal');
    }

    /**
     * بستن مدال پیشرفت
     */
    public function closeProgressModal()
    {
        $this->showProgressModal = false;
        $this->isSending = false;
        $this->isCancelled = false;
        $this->sendingProgress = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
            'current' => null,
            'current_index' => 0,
            'completed' => false,
            'result_message' => null,
        ];
        $this->sendResults = [];
        $this->dispatch('hide-progress-modal');
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

        // پیدا کردن بزرگترین index برای ساخت آرایه کامل
        $maxIndex = !empty($usedIndices) ? max($usedIndices) : -1;
        
        // ساخت آرایه کامل از 0 تا maxIndex
        // API ملی پیامک انتظار دارد که متغیرها به ترتیب {0}, {1}, {2}, ... باشند
        // حتی اگر در الگو {0}, {2}, {3} باشد، باید آرایه [value0, '', value2, value3] باشد
        for ($i = 0; $i <= $maxIndex; $i++) {
            $code = '{' . $i . '}';
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
        // اگر residentData یک Model Resident است، آن را به array تبدیل می‌کنیم
        if ($residentData instanceof \App\Models\Resident) {
            $residentData = $residentData->toArray();
        }
        
        // ساخت ساختار داده با استفاده از فیلدهای واقعی دیتابیس
        // مهم: باید تمام فیلدهای دیتابیس را نگه داریم تا getVariableValue بتواند از table_field استفاده کند
        return [
            'resident' => [
                'id' => $residentData['id'] ?? $residentData['resident_id'] ?? null,
                'resident_id' => $residentData['resident_id'] ?? null,
                // نگه داشتن نام فیلدهای واقعی دیتابیس
                'resident_full_name' => $residentData['resident_full_name'] ?? '',
                'resident_phone' => $residentData['resident_phone'] ?? '',
                'resident_age' => $residentData['resident_age'] ?? '',
                'resident_job' => $residentData['resident_job'] ?? '',
                'contract_payment_date_jalali' => $residentData['contract_payment_date_jalali'] ?? '',
                'contract_start_date_jalali' => $residentData['contract_start_date_jalali'] ?? '',
                'contract_end_date_jalali' => $residentData['contract_end_date_jalali'] ?? '',
                // همچنین نام‌های جایگزین برای سازگاری
                'full_name' => $residentData['resident_full_name'] ?? $residentData['full_name'] ?? $residentData['name'] ?? '',
                'name' => $residentData['resident_full_name'] ?? $residentData['full_name'] ?? $residentData['name'] ?? '',
                'phone' => $residentData['resident_phone'] ?? $residentData['phone'] ?? '',
                'national_id' => $residentData['national_id'] ?? $residentData['national_code'] ?? '',
                'national_code' => $residentData['national_id'] ?? $residentData['national_code'] ?? '',
                'payment_date_jalali' => $residentData['contract_payment_date_jalali'] ?? '',
            ],
            'unit' => [
                'id' => $residentData['unit_id'] ?? null,
                'name' => $residentData['unit_name'] ?? '',
                'code' => $residentData['unit_code'] ?? '',
            ],
            'room' => [
                'id' => $residentData['room_id'] ?? null,
                'name' => $residentData['room_name'] ?? '',
                'code' => $residentData['room_code'] ?? '',
            ],
            'bed' => [
                'id' => $residentData['bed_id'] ?? null,
                'name' => $residentData['bed_name'] ?? '',
                'code' => $residentData['bed_code'] ?? '',
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
                // فیلدهای مستقیم resident
                // table_field می‌تواند به صورت مستقیم (مثل full_name) یا با prefix (مثل resident_full_name) باشد
                
                // اول سعی می‌کنیم با همان نام table_field از resident بخوانیم
                $value = $residentData['resident'][$field] ?? '';
                
                // اگر پیدا نشد و table_field با resident_ شروع می‌شود، prefix را حذف می‌کنیم
                if (empty($value) && strpos($field, 'resident_') === 0) {
                    $keyWithoutPrefix = substr($field, 9); // حذف 'resident_' از ابتدا
                    $value = $residentData['resident'][$keyWithoutPrefix] ?? '';
                }
                
                // اگر هنوز پیدا نشد، سعی می‌کنیم نام‌های جایگزین را بررسی کنیم
                if (empty($value)) {
                    // برای full_name
                    if ($field === 'full_name' || $field === 'name' || $field === 'resident_full_name') {
                        $value = $residentData['resident']['full_name'] ?? 
                                 $residentData['resident']['name'] ?? 
                                 $residentData['resident']['resident_full_name'] ?? '';
                    }
                    // برای phone
                    elseif ($field === 'phone' || $field === 'resident_phone') {
                        $value = $residentData['resident']['phone'] ?? 
                                 $residentData['resident']['resident_phone'] ?? '';
                    }
                    // برای national_id
                    elseif ($field === 'national_id' || $field === 'national_code') {
                        $value = $residentData['resident']['national_id'] ?? 
                                 $residentData['resident']['national_code'] ?? '';
                    }
                    // برای contract_payment_date_jalali
                    elseif ($field === 'contract_payment_date_jalali' || $field === 'payment_date_jalali') {
                        $value = $residentData['resident']['contract_payment_date_jalali'] ?? 
                                 $residentData['resident']['payment_date_jalali'] ?? '';
                    }
                    // برای سایر فیلدها، سعی می‌کنیم مستقیماً از resident بخوانیم
                    else {
                        // اگر table_field با resident_ شروع می‌شود، prefix را حذف می‌کنیم
                        if (strpos($field, 'resident_') === 0) {
                            $keyWithoutPrefix = substr($field, 9);
                            $value = $residentData['resident'][$keyWithoutPrefix] ?? '';
                        } else {
                            $value = $residentData['resident'][$field] ?? '';
                        }
                    }
                }
                
                if (!is_string($value)) {
                    $value = (string)$value;
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

    public function render()
    {
        $residents = $this->getFilteredResidentsQuery()
            ->orderBy('resident_full_name', 'asc')
            ->paginate($this->perPage);

        // محاسبه تعداد انتخاب شده
        $selectedCount = count($this->selectedResidents);

        // بررسی اینکه آیا همه افراد فیلتر شده انتخاب شده‌اند
        $allFilteredIds = $this->getFilteredResidentsQuery()->pluck('id')->toArray();
        $allSelected = !empty($allFilteredIds) && count($allFilteredIds) === count($this->selectedResidents) && empty(array_diff($allFilteredIds, $this->selectedResidents));
        if ($allSelected && !$this->selectAll) {
            $this->selectAll = true;
        } elseif (!$allSelected && $this->selectAll) {
            $this->selectAll = false;
        }

        return view('livewire.residents.group-sms', [
            'residents' => $residents,
            'selectedCount' => $selectedCount,
            'filteredCount' => $this->getFilteredResidentsQuery()->count(),
        ]);
    }
}

