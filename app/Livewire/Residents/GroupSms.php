<?php

namespace App\Livewire\Residents;

use App\Models\Resident;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
use App\Models\SenderNumber;
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
    public $sendingProgress = [
        'total' => 0,
        'sent' => 0,
        'failed' => 0,
        'current' => null,
    ];

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
        // فقط الگوهایی که فعال، تایید شده و دارای pattern_code هستند
        $this->patterns = Pattern::where('is_active', true)
            ->where('status', 'approved') // فقط الگوهای تایید شده
            ->whereNotNull('pattern_code') // فقط الگوهایی که pattern_code دارند
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
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'هیچ اقامت‌گری با شماره تلفن در لیست انتخاب شده یافت نشد.'
            ]);
            return;
        }

        // دریافت شماره فرستنده و API Key
        $senderNumberObj = null;
        $apiKey = null;
        if ($this->selectedSenderNumberId) {
            $senderNumberObj = SenderNumber::find($this->selectedSenderNumberId);
            if ($senderNumberObj && !empty($senderNumberObj->api_key)) {
                $apiKey = $senderNumberObj->api_key;
            }
        }
        
        // اگر API Key از شماره فرستنده تنظیم نشد یا خالی بود، از config استفاده می‌کنیم
        if (empty($apiKey)) {
            $apiKey = config('services.melipayamak.console_api_key');
        }
        
        // لاگ API Key برای دیباگ
        Log::info('GroupSms - API Key configuration', [
            'has_sender_number' => !is_null($senderNumberObj),
            'sender_number_id' => $this->selectedSenderNumberId,
            'api_key_from_sender' => !empty($senderNumberObj?->api_key),
            'api_key_from_config' => !empty(config('services.melipayamak.console_api_key')),
            'api_key_length' => $apiKey ? strlen($apiKey) : 0,
            'api_key_preview' => $apiKey ? substr($apiKey, 0, 8) . '...' : 'empty',
        ]);
        
        // توجه: برای SOAP API از username/password استفاده می‌شود که در MelipayamakService تنظیم شده
        // برای REST API (sendByBaseNumber2) از console_api_key استفاده می‌شود

        $melipayamakService = new MelipayamakService();
        $residentService = new ResidentService();
        
        // Reset progress
        $this->isSending = true;
        $this->sendingProgress = [
            'total' => $selectedResidents->count(),
            'sent' => 0,
            'failed' => 0,
            'current' => null,
        ];

        // ارسال به صورت تکی در حلقه
        foreach ($selectedResidents as $resident) {
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
                        Log::error('Error getting resident data from API', [
                            'resident_id' => $resident->resident_id,
                            'error' => $e->getMessage(),
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
                    'unit_id' => $resident->unit_id,
                    'unit_name' => $resident->unit_name,
                    'room_id' => $resident->room_id,
                    'room_name' => $resident->room_name,
                    'bed_id' => $resident->bed_id,
                    'bed_name' => $resident->bed_name,
                ];

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

                // ارسال پیامک با الگو
                // استراتژی: ابتدا SOAP API را امتحان می‌کنیم (اگر extension فعال باشد)
                // اگر SOAP خطا داد یا extension فعال نبود، از REST API استفاده می‌کنیم
                
                $result = null;
                
                // بررسی اینکه SOAP extension فعال است
                if (extension_loaded('soap')) {
                    Log::info('GroupSms - Trying SOAP API first');
                    
                    // استفاده از SOAP API (از username/password در config استفاده می‌کند)
                    $soapResult = $melipayamakService->sendByBaseNumber(
                        $resident->resident_phone,
                        $bodyId,
                        $variables,
                        $senderNumberObj ? $senderNumberObj->number : null,
                        null // برای SOAP از apiKey استفاده نمی‌کنیم، از username/password استفاده می‌شود
                    );
                    
                    // اگر SOAP موفق بود، از نتیجه آن استفاده می‌کنیم
                    if ($soapResult['success']) {
                        $result = $soapResult;
                        Log::info('GroupSms - SOAP API succeeded');
                    } else {
                        Log::warning('GroupSms - SOAP API failed, trying REST API', [
                            'soap_error' => $soapResult['message'] ?? 'Unknown error',
                            'response_code' => $soapResult['response_code'] ?? null,
                        ]);
                    }
                } else {
                    Log::warning('GroupSms - SOAP extension not loaded, using REST API');
                }
                
                // اگر SOAP استفاده نشد یا خطا داد، از REST API استفاده می‌کنیم
                if (!$result || !$result['success']) {
                    // برای REST API از console_api_key استفاده می‌کنیم
                    // اگر apiKey از SenderNumber موجود باشد و معتبر باشد، استفاده می‌کنیم
                    // در غیر این صورت مستقیماً از config استفاده می‌کنیم
                    $restApiKey = $apiKey;
                    if (empty($restApiKey) || strlen($restApiKey) < 20) {
                        // اگر API Key از SenderNumber خالی یا نامعتبر است، از config استفاده می‌کنیم
                        $restApiKey = config('services.melipayamak.console_api_key');
                    }
                    
                    Log::info('GroupSms - Using REST API (console) with API key', [
                        'api_key_source' => empty($apiKey) || strlen($apiKey) < 20 ? 'config' : 'sender_number',
                        'api_key_length' => $restApiKey ? strlen($restApiKey) : 0,
                        'api_key_preview' => $restApiKey ? substr($restApiKey, 0, 8) . '...' : 'empty',
                        'config_console_api_key_exists' => !empty(config('services.melipayamak.console_api_key')),
                    ]);
                    
                    // استفاده از REST API (console)
                    $restResult = $melipayamakService->sendByBaseNumber2(
                        $resident->resident_phone,
                        $bodyId,
                        $variables,
                        $senderNumberObj ? $senderNumberObj->number : null,
                        $restApiKey // این API Key باید از نوع console_api_key باشد
                    );
                    
                    $result = $restResult;
                    
                    Log::info('GroupSms - REST API result', [
                        'success' => $result['success'] ?? false,
                        'message' => $result['message'] ?? 'No message',
                        'response_code' => $result['response_code'] ?? null,
                    ]);
                }
                
                Log::info('GroupSms - Final API result', [
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'No message',
                    'response_code' => $result['response_code'] ?? null,
                ]);

                // لاگ نتیجه ارسال
                Log::info('GroupSms - SMS send result', [
                    'resident_id' => $resident->id,
                    'phone' => $resident->resident_phone,
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'Unknown error',
                    'response_code' => $result['response_code'] ?? null,
                ]);

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
            
            $this->dispatch('$refresh');
        }

        $this->isSending = false;
        $this->sendingProgress['current'] = null;

        // لاگ نتیجه نهایی
        Log::info('GroupSms - Final sending result', [
            'total' => $this->sendingProgress['total'],
            'sent' => $this->sendingProgress['sent'],
            'failed' => $this->sendingProgress['failed'],
        ]);

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

