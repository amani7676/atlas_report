<?php

namespace App\Listeners;

use App\Events\ResidentReportCreated;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\Resident;
use App\Models\Report;
use App\Models\ResidentReport;
use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;
use App\Services\ResidentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendViolationSms
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ResidentReportCreated $event): void
    {
        try {
            $residentReport = $event->residentReport;
            
            Log::info('SendViolationSms listener started', [
                'resident_report_id' => $residentReport->id ?? null,
            ]);
            
            // بررسی اینکه آیا پیامک قبلاً ارسال شده است (برای جلوگیری از ارسال دوبار)
            // اگر has_been_sent true باشد، یعنی پیامک از Units.php ارسال شده است
            if ($residentReport->has_been_sent) {
                Log::info('SMS already sent from Units.php - skipping Event Listener', [
                    'resident_report_id' => $residentReport->id,
                    'report_id' => $residentReport->report_id,
                    'has_been_sent' => true,
                ]);
                return;
            }
            
            // بررسی اینکه آیا SmsMessageResident برای این report_id و resident_id قبلاً ایجاد شده است
            $existingSms = SmsMessageResident::where('report_id', $residentReport->report_id)
                ->where(function($query) use ($residentReport) {
                    if ($residentReport->resident_id) {
                        $query->where('resident_id', $residentReport->resident_id);
                    }
                    if ($residentReport->phone) {
                        $query->orWhere('phone', $residentReport->phone);
                    }
                })
                ->where('is_pattern', true)
                ->first();
            
            if ($existingSms) {
                Log::info('SMS already sent - SmsMessageResident exists - skipping Event Listener', [
                    'resident_report_id' => $residentReport->id,
                    'report_id' => $residentReport->report_id,
                    'sms_message_resident_id' => $existingSms->id,
                    'sms_status' => $existingSms->status,
                ]);
                return;
            }
            
            // بارگذاری روابط
            $residentReport->load(['report.category', 'resident']);
            
            Log::info('ResidentReport loaded', [
                'resident_report_id' => $residentReport->id,
                'report_id' => $residentReport->report_id,
                'resident_id' => $residentReport->resident_id,
                'has_report' => $residentReport->report ? 'yes' : 'no',
                'has_resident' => $residentReport->resident ? 'yes' : 'no',
            ]);
            
            if (!$residentReport->report) {
                Log::warning('ResidentReport has no report', [
                    'resident_report_id' => $residentReport->id,
                    'report_id' => $residentReport->report_id,
                ]);
                return;
            }

            $report = $residentReport->report;
            
            // دریافت اولین الگوی فعال مرتبط با گزارش (فقط یک الگو)
            // الگو باید هم در pivot table فعال باشد و هم خود الگو فعال باشد
            $pattern = $report->activePatterns()
                ->where('patterns.is_active', true)
                ->whereNotNull('patterns.pattern_code')
                ->orderBy('report_pattern.sort_order')
                ->first();
            
            // اگر الگویی پیدا نشد، بررسی می‌کنیم که آیا اصلاً الگویی به گزارش وصل شده یا نه
            if (!$pattern) {
                $allPatternsCount = $report->patterns()->count();
                Log::info('No active pattern found', [
                    'report_id' => $report->id,
                    'all_patterns_count' => $allPatternsCount,
                    'active_patterns_count' => $report->activePatterns()->count(),
                ]);
            }
            
            Log::info('Pattern check', [
                'report_id' => $report->id,
                'report_title' => $report->title,
                'pattern_found' => $pattern ? 'yes' : 'no',
                'pattern_id' => $pattern->id ?? null,
                'pattern_code' => $pattern->pattern_code ?? null,
                'active_patterns_count' => $report->activePatterns()
                    ->where('patterns.is_active', true)
                    ->whereNotNull('patterns.pattern_code')
                    ->count(),
            ]);
            
            // اگر الگویی پیدا نشد یا pattern_code ندارد، پیامک ارسال نمی‌شود
            if (!$pattern || !$pattern->pattern_code) {
                Log::info('No active pattern found for report - SMS will not be sent', [
                    'report_id' => $report->id,
                    'report_title' => $report->title,
                    'resident_report_id' => $residentReport->id,
                    'pattern_exists' => $pattern ? 'yes' : 'no',
                    'pattern_code_exists' => ($pattern && $pattern->pattern_code) ? 'yes' : 'no',
                    'pattern_code' => $pattern->pattern_code ?? 'NULL',
                    'active_patterns_count' => $report->activePatterns()
                        ->where('patterns.is_active', true)
                        ->whereNotNull('patterns.pattern_code')
                        ->count(),
                ]);
                return; // پیامک ارسال نمی‌شود
            }
            
            Log::info('Pattern selected for SMS', [
                'pattern_id' => $pattern->id,
                'pattern_title' => $pattern->title,
                'pattern_code' => $pattern->pattern_code,
            ]);

            // دریافت اطلاعات resident
            $resident = $residentReport->resident;
            $phone = null;
            $residentName = null;
            
            if ($resident) {
                $phone = $resident->resident_phone;
                $residentName = $resident->resident_full_name;
            } else {
                // اگر resident پیدا نشد، از فیلدهای ذخیره شده در ResidentReport استفاده می‌کنیم
                $phone = $residentReport->phone ?? null;
                $residentName = $residentReport->resident_name ?? null;
                
                Log::warning('ResidentReport has no resident relation, using stored data', [
                    'resident_report_id' => $residentReport->id,
                    'resident_id' => $residentReport->resident_id,
                    'stored_phone' => $phone,
                    'stored_name' => $residentName,
                ]);
            }

            if (empty($phone)) {
                Log::warning('No phone number available', [
                    'resident_report_id' => $residentReport->id,
                    'resident_id' => $residentReport->resident_id,
                    'has_resident' => $resident ? 'yes' : 'no',
                ]);
                return;
            }

            Log::info('Resident info ready', [
                'resident_id' => $resident ? $resident->id : null,
                'resident_name' => $residentName,
                'phone' => $phone,
            ]);

            // استخراج متغیرها - اگر resident وجود نداشت، یک object موقت می‌سازیم
            if (!$resident) {
                // ساخت یک object موقت برای resident
                $resident = (object)[
                    'id' => $residentReport->resident_id,
                    'resident_id' => $residentReport->resident_id,
                    'full_name' => $residentName,
                    'resident_full_name' => $residentName,
                    'phone' => $phone,
                    'resident_phone' => $phone,
                    'unit_id' => $residentReport->unit_id,
                    'unit_name' => $residentReport->unit_name ?? null,
                    'room_id' => $residentReport->room_id,
                    'room_name' => $residentReport->room_name ?? null,
                    'bed_id' => $residentReport->bed_id,
                    'bed_name' => $residentReport->bed_name ?? null,
                ];
            } else {
                // اگر resident وجود دارد، مطمئن شویم که فیلدهای قدیمی هم موجود هستند (برای سازگاری)
                if (!isset($resident->full_name) && isset($resident->resident_full_name)) {
                    $resident->full_name = $resident->resident_full_name;
                }
                if (!isset($resident->phone) && isset($resident->resident_phone)) {
                    $resident->phone = $resident->resident_phone;
                }
            }
            
            $variables = $this->extractPatternVariables($pattern, $resident, $report);
            
            Log::info('Pattern variables extracted', [
                'pattern_id' => $pattern->id,
                'variables_count' => count($variables),
                'variables' => $variables,
            ]);

            // بررسی اینکه آیا قبلاً برای این ResidentReport و Pattern پیامک ارسال شده است
            // بررسی هم برای status='sent' و هم برای status='pending' (که ممکن است در حال ارسال باشد)
            $existingSms = SmsMessageResident::where('report_id', $report->id)
                ->where('pattern_id', $pattern->id)
                ->where('resident_id', $residentReport->resident_id)
                ->where('created_at', '>=', now()->subMinutes(5)) // فقط در 5 دقیقه گذشته
                ->whereIn('status', ['sent', 'pending']) // بررسی هم sent و هم pending
                ->first();
            
            Log::info('Checking existing SMS', [
                'report_id' => $report->id,
                'pattern_id' => $pattern->id,
                'resident_id' => $residentReport->resident_id,
                'existing_sms_found' => $existingSms ? 'yes' : 'no',
                'existing_sms_id' => $existingSms->id ?? null,
                'existing_sms_status' => $existingSms->status ?? null,
            ]);
            
            if ($existingSms) {
                Log::info('SendViolationSms listener skipped - SMS already exists for this report and pattern', [
                    'resident_report_id' => $residentReport->id,
                    'pattern_id' => $pattern->id,
                    'existing_sms_id' => $existingSms->id,
                    'existing_sms_status' => $existingSms->status,
                ]);
                return;
            }
            
            // استفاده از lock برای جلوگیری از ارسال دوباره
            $lockKey = 'sms_send_' . $residentReport->id . '_' . $pattern->id;
            $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 30); // 30 ثانیه
            
            Log::info('Attempting to acquire lock', [
                'lock_key' => $lockKey,
                'resident_report_id' => $residentReport->id,
                'pattern_id' => $pattern->id,
            ]);
            
            if (!$lock->get()) {
                Log::warning('SendViolationSms listener skipped - Lock acquired by another process', [
                    'resident_report_id' => $residentReport->id,
                    'pattern_id' => $pattern->id,
                    'lock_key' => $lockKey,
                ]);
                return;
            }
            
            Log::info('Lock acquired successfully', [
                'lock_key' => $lockKey,
                'resident_report_id' => $residentReport->id,
                'pattern_id' => $pattern->id,
            ]);
            
            try {
                // بررسی دوباره بعد از گرفتن lock (double-check)
                $existingSmsAfterLock = SmsMessageResident::where('report_id', $report->id)
                    ->where('pattern_id', $pattern->id)
                    ->where('resident_id', $residentReport->resident_id)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->whereIn('status', ['sent', 'pending'])
                    ->first();
                
                if ($existingSmsAfterLock) {
                    Log::info('SendViolationSms listener skipped - SMS already exists after lock', [
                        'resident_report_id' => $residentReport->id,
                        'pattern_id' => $pattern->id,
                        'existing_sms_id' => $existingSmsAfterLock->id,
                    ]);
                    return;
                }
                
                // ایجاد رکورد در sms_message_residents
                $smsMessageResident = SmsMessageResident::create([
                    'sms_message_id' => null,
                    'report_id' => $report->id,
                    'pattern_id' => $pattern->id,
                    'is_pattern' => true,
                    'pattern_variables' => implode(';', $variables),
                    'resident_id' => $residentReport->resident_id,
                    'resident_name' => $residentName,
                    'phone' => $phone,
                    'title' => $pattern->title,
                    'description' => $pattern->text,
                    'status' => 'pending',
                ]);
                
                Log::info('SmsMessageResident created', [
                    'sms_message_resident_id' => $smsMessageResident->id,
                ]);

                // ارسال پیامک - استفاده از sendByBaseNumber (SOAP API) مشابه PatternTest و ExpiredToday
                $melipayamakService = new MelipayamakService();
                $bodyId = (int)$pattern->pattern_code;
                
                // دریافت شماره فرستنده (اختیاری)
                $senderNumber = \App\Models\SenderNumber::getActivePatternNumbers()->first();
                $senderNumberValue = $senderNumber ? $senderNumber->number : null;
                $apiKey = $senderNumber ? $senderNumber->api_key : null;

                Log::info('SendViolationSms - Sending SMS using sendByBaseNumber (SOAP API)', [
                    'phone' => $phone,
                    'body_id' => $bodyId,
                    'pattern_id' => $pattern->id,
                    'variables_count' => count($variables),
                    'variables' => $variables,
                    'sender_number' => $senderNumberValue,
                    'has_api_key' => !empty($apiKey),
                ]);

                // استفاده از sendByBaseNumber (SOAP API) مشابه PatternTest و ExpiredToday
                // این متد از username/password از config استفاده می‌کند و معمولاً قابل اعتمادتر است
                $result = $melipayamakService->sendByBaseNumber(
                    $phone,
                    $bodyId,
                    $variables,
                    $senderNumberValue, // شماره فرستنده (null = از config استفاده می‌شود)
                    $apiKey // API Key (اختیاری - برای SOAP API استفاده نمی‌شود)
                );
                
                Log::info('SendViolationSms - sendByBaseNumber result', [
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'No message',
                    'response_code' => $result['response_code'] ?? null,
                    'rec_id' => $result['rec_id'] ?? null,
                    'api_response' => $result['api_response'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                ]);

                // به‌روزرسانی وضعیت
                if ($result['success']) {
                    $smsMessageResident->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'response_code' => $result['response_code'] ?? null,
                        'rec_id' => $result['rec_id'] ?? null,
                        'api_response' => $result['api_response'] ?? null,
                        'raw_response' => $result['raw_response'] ?? null,
                    ]);

                    // به‌روزرسانی has_been_sent در resident_report
                    $residentReport->update(['has_been_sent' => true]);

                    Log::info('Violation SMS sent successfully', [
                        'resident_report_id' => $residentReport->id,
                        'resident_id' => $residentReport->resident_id,
                        'pattern_id' => $pattern->id,
                        'sms_message_resident_id' => $smsMessageResident->id,
                    ]);
                } else {
                    $smsMessageResident->update([
                        'status' => 'failed',
                        'error_message' => $result['message'] ?? 'خطا در ارسال',
                        'response_code' => $result['response_code'] ?? null,
                        'api_response' => $result['api_response'] ?? null,
                        'raw_response' => $result['raw_response'] ?? null,
                    ]);

                    Log::error('Failed to send violation SMS', [
                        'resident_report_id' => $residentReport->id,
                        'resident_id' => $residentReport->resident_id,
                        'pattern_id' => $pattern->id,
                        'error' => $result['message'] ?? 'خطا در ارسال',
                        'result' => $result,
                    ]);
                }
            } finally {
                // آزاد کردن lock - باید بعد از ارسال پیامک آزاد شود
                $lock->release();
            }
        } catch (\Exception $e) {
            Log::error('Error in SendViolationSms listener', [
                'resident_report_id' => $event->residentReport->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * استخراج متغیرها از الگو - مشابه PatternManual و ExpiredToday
     */
    protected function extractPatternVariables(Pattern $pattern, $resident, Report $report)
    {
        // پیدا کردن تمام متغیرها در الگو (مثل {0}, {1}, {2})
        preg_match_all('/\{(\d+)\}/', $pattern->text, $matches);
        
        if (empty($matches[1])) {
            return []; // اگر متغیری وجود نداشت
        }

        // دریافت اطلاعات resident
        $residentData = $this->getResidentData($resident);

        // دریافت اطلاعات گزارش
        $reportData = [
            'title' => $report->title,
            'description' => $report->description,
            'category_name' => $report->category->name ?? '',
            'negative_score' => $report->negative_score,
            'type' => $report->type ?? 'violation',
        ];

        // بر اساس سیستم جدید:
        // 1. بریم جدول pattern_variables ببین با کدام کد الگویی یکی هست
        // 2. از اون طریق رو جدول pattern_pattern_variables کد ها رو پیدا کن
        // 3. ببین چه فیلد برای اون کد ذخیره شده
        // 4. اون از جدولی که در جدول pattern_variables در فیلد table_name گذاشتیم پیدا کن و جایگذاری کن
        
        $patternVariable = PatternVariable::where('pattern_code', $pattern->pattern_code)
            ->where('is_active', true)
            ->first();
        
        Log::info('SendViolationSms - Pattern variable found', [
            'pattern_id' => $pattern->id,
            'pattern_code' => $pattern->pattern_code,
            'pattern_variable_id' => $patternVariable->id ?? null,
            'table_name' => $patternVariable->table_name ?? null,
        ]);
        
        if (!$patternVariable) {
            Log::error('SendViolationSms - No pattern variable found', [
                'pattern_code' => $pattern->pattern_code,
            ]);
            return [];
        }
        
        $result = [];
        $usedIndices = array_unique(array_map('intval', $matches[1]));
        sort($usedIndices);

        Log::debug('SendViolationSms - Extracting pattern variables', [
            'pattern_text' => $pattern->text,
            'used_indices' => $usedIndices,
            'resident_id' => $resident->id ?? $resident->resident_id ?? null,
            'report_id' => $report->id ?? null,
            'pattern_id' => $pattern->id,
        ]);

        // برای هر کد در پیام:
        foreach ($usedIndices as $index) {
            $code = '{' . $index . '}';
            
            Log::debug('SendViolationSms - Processing variable', [
                'code' => $code,
                'index' => $index,
                'pattern_variable_id' => $patternVariable->id,
            ]);
            
            // 2. از طریق متغیر بریم جدول pattern_pattern_variables کد رو پیدا کن
            $pivotData = DB::table('pattern_pattern_variables')
                ->where('pattern_id', $pattern->id)
                ->where('variable_code', $code)
                ->first();
            
            if ($pivotData && $pivotData->table_field) {
                // 3. فیلد مربوط به این کد رو پیدا کردیم
                $tableField = $pivotData->table_field;
                $tableName = $patternVariable->table_name; // 4. جدول اصلی از pattern_variables
                
                Log::debug('SendViolationSms - Found pivot data', [
                    'code' => $code,
                    'table_field' => $tableField,
                    'table_name' => $tableName,
                    'pivot_id' => $pivotData->id,
                ]);
                
                // 4. از جدول مشخص شده مقدار رو پیدا کن و جایگذاری کن
                $value = $this->getVariableValueFromTable($tableName, $tableField, $resident, $report);
                
                Log::debug('SendViolationSms - Variable value extracted', [
                    'code' => $code,
                    'table_name' => $tableName,
                    'table_field' => $tableField,
                    'value' => $value,
                    'value_length' => strlen($value),
                ]);
                
                // اطمینان از اینکه value یک رشته است
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                
                // اگر مقدار خالی است، لاگ می‌کنیم
                if (empty(trim($value))) {
                    Log::warning('SendViolationSms - Variable value is empty', [
                        'code' => $code,
                        'index' => $index,
                        'table_name' => $tableName,
                        'table_field' => $tableField,
                    ]);
                    $value = ''; // مقدار خالی - API باید آن را قبول کند
                }
                
                Log::info('SendViolationSms - Variable extracted successfully', [
                    'code' => $code,
                    'index' => $index,
                    'table_name' => $tableName,
                    'table_field' => $tableField,
                    'value' => $value,
                    'value_length' => strlen($value),
                ]);
                $result[] = $value;
            } else {
                // اگر کد در جدول pivot پیدا نشد
                Log::error('SendViolationSms - Variable code not found in pivot table', [
                    'code' => $code,
                    'index' => $index,
                    'pattern_id' => $pattern->id,
                    'pattern_variable_id' => $patternVariable->id,
                ]);
                $result[] = ''; // مقدار خالی برای کدهای پیدا نشده
            }
        }

        Log::debug('SendViolationSms - Pattern variables extracted', [
            'variables' => $result,
            'variables_count' => count($result),
        ]);

        return $result;
    }

    /**
     * دریافت اطلاعات resident
     */
    protected function getResidentData($resident)
    {
        // اگر resident یک object ساده است (نه مدل Eloquent)
        if (!($resident instanceof \App\Models\Resident)) {
            Log::info('SendViolationSms - Using simple resident object data', [
                'resident_id' => $resident->id ?? $resident->resident_id ?? null,
            ]);
            
            return [
                'resident' => [
                    'id' => $resident->resident_id ?? $resident->id ?? null,
                    'full_name' => $resident->full_name ?? $resident->resident_full_name ?? '',
                    'name' => $resident->full_name ?? $resident->resident_full_name ?? '',
                    'phone' => $resident->phone ?? $resident->resident_phone ?? '',
                    'contract_payment_date_jalali' => $resident->contract_payment_date_jalali ?? $resident->payment_date_jalali ?? '',
                    'payment_date_jalali' => $resident->contract_payment_date_jalali ?? $resident->payment_date_jalali ?? '',
                ],
                'unit' => [
                    'id' => $resident->unit_id ?? null,
                    'name' => $resident->unit_name ?? '',
                ],
                'room' => [
                    'id' => $resident->room_id ?? null,
                    'name' => $resident->room_name ?? '',
                ],
                'bed' => [
                    'id' => $resident->bed_id ?? null,
                    'name' => $resident->bed_name ?? '',
                ],
            ];
        }

        try {
            $residentService = new ResidentService();
            // استفاده از resident_id (نه id) برای جستجو
            $residentApiId = $resident->resident_id ?? $resident->id;
            $data = $residentService->getResidentById($residentApiId);
            
            Log::info('SendViolationSms - Resident data fetched from API', [
                'resident_id' => $resident->id,
                'resident_api_id' => $residentApiId,
                'data_found' => $data ? 'yes' : 'no',
            ]);
            
            if ($data) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('SendViolationSms - Error getting resident data from API', [
                'resident_id' => $resident->id,
                'resident_id_field' => $resident->resident_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Fallback data - استفاده از داده‌های دیتابیس
        Log::info('SendViolationSms - Using fallback resident data from DB', [
            'resident_id' => $resident->id,
        ]);
        
        return [
            'resident' => [
                'id' => $resident->resident_id ?? $resident->id,
                'full_name' => $resident->resident_full_name ?? $resident->full_name ?? '',
                'name' => $resident->resident_full_name ?? $resident->full_name ?? '',
                'phone' => $resident->resident_phone ?? $resident->phone ?? '',
                'contract_payment_date_jalali' => $resident->contract_payment_date_jalali ?? $resident->payment_date_jalali ?? '',
                'payment_date_jalali' => $resident->contract_payment_date_jalali ?? $resident->payment_date_jalali ?? '',
            ],
            'unit' => [
                'id' => $resident->unit_id ?? null,
                'name' => $resident->unit_name ?? '',
            ],
            'room' => [
                'id' => $resident->room_id ?? null,
                'name' => $resident->room_name ?? '',
            ],
            'bed' => [
                'id' => $resident->bed_id ?? null,
                'name' => $resident->bed_name ?? '',
            ],
        ];
    }

    /**
     * استخراج مقدار متغیر از جدول مشخص شده
     * بر اساس سیستم جدید: از جدولی که در pattern_variables.table_name مشخص شده
     */
    private function getVariableValueFromTable($tableName, $tableField, $resident, $report)
    {
        if (empty($tableName) || empty($tableField)) {
            return '';
        }

        Log::debug('SendViolationSms - Getting value from table', [
            'table_name' => $tableName,
            'table_field' => $tableField,
            'resident_id' => $resident->id ?? $resident->resident_id ?? null,
        ]);

        switch ($tableName) {
            case 'residents':
                return $this->getResidentFieldValue($tableField, $resident);
            case 'reports':
                return $this->getReportFieldValue($tableField, $report);
            case 'units':
                return $this->getUnitFieldValue($tableField, $resident);
            case 'rooms':
                return $this->getRoomFieldValue($tableField, $resident);
            case 'beds':
                return $this->getBedFieldValue($tableField, $resident);
            default:
                Log::warning('SendViolationSms - Unknown table name', [
                    'table_name' => $tableName,
                    'table_field' => $tableField,
                ]);
                return '';
        }
    }

    /**
     * دریافت مقدار از جدول residents
     */
    private function getResidentFieldValue($field, $resident)
    {
        switch ($field) {
            case 'resident_full_name':
            case 'full_name':
            case 'name':
                return $resident->resident_full_name ?? $resident->full_name ?? '';
            case 'resident_phone':
            case 'phone':
                return $resident->resident_phone ?? $resident->phone ?? '';
            case 'contract_payment_date_jalali':
            case 'payment_date_jalali':
                return $resident->contract_payment_date_jalali ?? $resident->payment_date_jalali ?? '';
            case 'room_name':
                return $resident->room_name ?? '';
            case 'bed_name':
                return $resident->bed_name ?? '';
            case 'unit_name':
                return $resident->unit_name ?? '';
            default:
                return $resident->$field ?? '';
        }
    }

    /**
     * دریافت مقدار از جدول reports
     */
    private function getReportFieldValue($field, $report)
    {
        switch ($field) {
            case 'title':
                return $report->title ?? '';
            case 'description':
                return $report->description ?? '';
            case 'category_name':
                return $report->category->name ?? '';
            case 'negative_score':
                return (string)($report->negative_score ?? '');
            case 'type':
                return $report->type ?? '';
            default:
                return $report->$field ?? '';
        }
    }

    /**
     * دریافت مقدار از جدول units
     */
    private function getUnitFieldValue($field, $resident)
    {
        switch ($field) {
            case 'name':
                return $resident->unit_name ?? '';
            case 'id':
                return (string)($resident->unit_id ?? '');
            default:
                return '';
        }
    }

    /**
     * دریافت مقدار از جدول rooms
     */
    private function getRoomFieldValue($field, $resident)
    {
        switch ($field) {
            case 'name':
                return $resident->room_name ?? '';
            case 'id':
                return (string)($resident->room_id ?? '');
            default:
                return '';
        }
    }

    /**
     * دریافت مقدار از جدول beds
     */
    private function getBedFieldValue($field, $resident)
    {
        switch ($field) {
            case 'name':
                return $resident->bed_name ?? '';
            case 'id':
                return (string)($resident->bed_id ?? '');
            default:
                return '';
        }
    }

    /**
     * استخراج مقدار متغیر بر اساس نام فیلد
     */
    private function getVariableValueByField($tableField, $residentData, $reportData)
    {
        if (empty($tableField)) {
            return '';
        }

        // اگر فیلد با پیشوندهای خاص شروع می‌شود
        if (strpos($tableField, 'resident_') === 0) {
            $key = substr($tableField, 9); // حذف پیشوند resident_
            return $residentData['resident'][$key] ?? '';
        } elseif (strpos($tableField, 'unit_') === 0) {
            $key = substr($tableField, 5); // حذف پیشوند unit_
            return $residentData['unit'][$key] ?? '';
        } elseif (strpos($tableField, 'room_') === 0) {
            $key = substr($tableField, 5); // حذف پیشوند room_
            return $residentData['room'][$key] ?? '';
        } elseif (strpos($tableField, 'bed_') === 0) {
            $key = substr($tableField, 4); // حذف پیشوند bed_
            return $residentData['bed'][$key] ?? '';
        } elseif (strpos($tableField, 'report_') === 0) {
            $key = substr($tableField, 7); // حذف پیشوند report_
            return $reportData[$key] ?? '';
        } else {
            // اگر پیشوند خاصی ندارد، از resident استفاده می‌کنیم
            return $residentData['resident'][$tableField] ?? '';
        }
    }

    /**
     * دریافت مقدار متغیر - مشابه PatternManual و ExpiredToday
     */
    protected function getVariableValue($variable, $residentData, $reportData)
    {
        // اگر متغیر از نوع اختصاصی است و table_field در pivot دارد، از آن استفاده می‌کنیم
        if (isset($variable->pivot_table_field)) {
            $field = $variable->pivot_table_field;
        } else {
            $field = $variable->table_field ?? '';
        }
        
        $type = $variable->variable_type ?? 'user';

        if ($type === 'user') {
            // فیلدهای کاربر
            if (strpos($field, 'unit_') === 0) {
                $key = substr($field, 5); // حذف 'unit_' از ابتدا
                $value = $residentData['unit'][$key] ?? '';
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                return $value;
            } elseif (strpos($field, 'room_') === 0) {
                $key = substr($field, 5); // حذف 'room_' از ابتدا
                $value = $residentData['room'][$key] ?? '';
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                return $value;
            } elseif (strpos($field, 'bed_') === 0) {
                $key = substr($field, 4); // حذف 'bed_' از ابتدا
                $value = $residentData['bed'][$key] ?? '';
                if (!is_string($value)) {
                    $value = (string)$value;
                }
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
                return $value;
            } else {
                $value = $reportData[$field] ?? '';
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                return $value;
            }
        } elseif ($type === 'general') {
            if ($field === 'today') {
                $value = $this->formatJalaliDate(now()->toDateString());
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                return $value;
            }
        }

        return '';
    }

    /**
     * تبدیل تاریخ میلادی به شمسی
     */
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
}

