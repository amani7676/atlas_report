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
use Illuminate\Support\Facades\Log;

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
                $phone = $resident->phone;
                $residentName = $resident->full_name;
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
                    'phone' => $phone,
                    'unit_id' => $residentReport->unit_id,
                    'unit_name' => $residentReport->unit_name ?? null,
                    'room_id' => $residentReport->room_id,
                    'room_name' => $residentReport->room_name ?? null,
                    'bed_id' => $residentReport->bed_id,
                    'bed_name' => $residentReport->bed_name ?? null,
                ];
            }
            
            $variables = $this->extractPatternVariables($pattern, $resident, $report);
            
            Log::info('Pattern variables extracted', [
                'pattern_id' => $pattern->id,
                'variables_count' => count($variables),
                'variables' => $variables,
            ]);

            // بررسی اینکه آیا قبلاً برای این ResidentReport و Pattern پیامک ارسال شده است
            $existingSms = SmsMessageResident::where('report_id', $report->id)
                ->where('pattern_id', $pattern->id)
                ->where('resident_id', $residentReport->resident_id)
                ->where('status', 'sent')
                ->where('created_at', '>=', now()->subMinutes(5)) // فقط در 5 دقیقه گذشته
                ->first();
            
            if ($existingSms) {
                Log::info('SendViolationSms listener skipped - SMS already sent for this report and pattern', [
                    'resident_report_id' => $residentReport->id,
                    'pattern_id' => $pattern->id,
                    'existing_sms_id' => $existingSms->id,
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

            // ارسال پیامک
            $melipayamakService = new MelipayamakService();
            $bodyId = (int)$pattern->pattern_code;
            
            // دریافت شماره فرستنده
            $senderNumber = \App\Models\SenderNumber::getActivePatternNumbers()->first();
            $senderNumberValue = $senderNumber ? $senderNumber->number : null;
            $apiKey = $senderNumber ? $senderNumber->api_key : null;

            Log::info('Sending SMS', [
                'phone' => $phone,
                'body_id' => $bodyId,
                'pattern_id' => $pattern->id,
                'variables_count' => count($variables),
                'sender_number' => $senderNumberValue,
                'has_api_key' => !empty($apiKey),
                'api_key_length' => $apiKey ? strlen($apiKey) : 0,
            ]);

            // تلاش اول: استفاده از SOAP API
            $result = $melipayamakService->sendByBaseNumber(
                $phone,
                $bodyId,
                $variables,
                $senderNumberValue,
                $apiKey
            );

            // اگر SOAP خطا داد، از REST API استفاده می‌کنیم
            if (!$result['success'] && (strpos($result['message'] ?? '', 'SOAP') !== false || strpos($result['message'] ?? '', 'WSDL') !== false)) {
                Log::warning('SOAP API failed, trying REST API (sendByBaseNumber2)', [
                    'soap_error' => $result['message'] ?? 'Unknown error',
                ]);
                
                $result = $melipayamakService->sendByBaseNumber2(
                    $phone,
                    $bodyId,
                    $variables,
                    $senderNumberValue,
                    $apiKey
                );
                
                Log::info('REST API result', [
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'No message',
                ]);
            }

            // به‌روزرسانی وضعیت
            if ($result['success']) {
                $smsMessageResident->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_code' => $result['response_code'] ?? null,
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
        } catch (\Exception $e) {
            Log::error('Error in SendViolationSms listener', [
                'resident_report_id' => $event->residentReport->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * استخراج متغیرها از الگو
     */
    protected function extractPatternVariables(Pattern $pattern, $resident, Report $report)
    {
        preg_match_all('/\{(\d+)\}/', $pattern->text, $matches);
        
        if (empty($matches[1])) {
            return [];
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

        // بارگذاری متغیرها از دیتابیس
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
                $value = $this->getVariableValue($variable, $residentData, $reportData);
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                if (empty(trim($value))) {
                    $value = '';
                }
                $result[] = $value;
            } else {
                $result[] = '';
            }
        }

        return $result;
    }

    /**
     * دریافت اطلاعات resident
     */
    protected function getResidentData($resident)
    {
        // اگر resident یک object ساده است (نه مدل Eloquent)
        if (!($resident instanceof \App\Models\Resident)) {
            Log::info('Using simple resident object data', [
                'resident_id' => $resident->id ?? $resident->resident_id ?? null,
            ]);
            
            return [
                'resident' => [
                    'id' => $resident->resident_id ?? $resident->id ?? null,
                    'full_name' => $resident->full_name ?? '',
                    'name' => $resident->full_name ?? '',
                    'phone' => $resident->phone ?? '',
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
            
            Log::info('Resident data fetched', [
                'resident_id' => $resident->id,
                'resident_api_id' => $residentApiId,
                'data_found' => $data ? 'yes' : 'no',
            ]);
            
            if ($data) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Error getting resident data in SendViolationSms', [
                'resident_id' => $resident->id,
                'resident_id_field' => $resident->resident_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Fallback data
        Log::info('Using fallback resident data', [
            'resident_id' => $resident->id,
        ]);
        
        return [
            'resident' => [
                'id' => $resident->resident_id ?? $resident->id,
                'full_name' => $resident->full_name,
                'name' => $resident->full_name,
                'phone' => $resident->phone,
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
     * دریافت مقدار متغیر
     */
    protected function getVariableValue($variable, $residentData, $reportData)
    {
        $field = $variable->table_field ?? '';
        $type = $variable->variable_type ?? 'user';

        if ($type === 'user') {
            if (strpos($field, 'unit_') === 0) {
                $key = substr($field, 5);
                $value = $residentData['unit'][$key] ?? '';
                return is_string($value) ? $value : (string)$value;
            } elseif (strpos($field, 'room_') === 0) {
                $key = substr($field, 5);
                $value = $residentData['room'][$key] ?? '';
                return is_string($value) ? $value : (string)$value;
            } elseif (strpos($field, 'bed_') === 0) {
                $key = substr($field, 4);
                $value = $residentData['bed'][$key] ?? '';
                return is_string($value) ? $value : (string)$value;
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
                return is_string($value) ? $value : (string)$value;
            }
        } elseif ($type === 'report' && $reportData) {
            if (strpos($field, 'category.') === 0) {
                $key = substr($field, 9);
                $value = $reportData['category_' . $key] ?? '';
                return is_string($value) ? $value : (string)$value;
            } else {
                $value = $reportData[$field] ?? '';
                return is_string($value) ? $value : (string)$value;
            }
        } elseif ($type === 'general') {
            if ($field === 'today') {
                $value = $this->formatJalaliDate(now()->toDateString());
                return is_string($value) ? $value : (string)$value;
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
