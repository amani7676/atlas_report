<?php

namespace App\Jobs;

use App\Models\AutoSms;
use App\Models\Resident;
use App\Models\SmsMessageResident;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Services\MelipayamakService;
use App\Services\ResidentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckAndSendAutoSms implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 1;
    public $timeout = 300;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // دریافت تمام پیامک‌های خودکار فعال که pattern_id دارند
            $autoSmsList = AutoSms::where('is_active', true)
                ->whereNotNull('pattern_id')
                ->with('pattern')
                ->get();

            foreach ($autoSmsList as $autoSms) {
                // بررسی نوع ارسال
                if ($autoSms->send_type === 'scheduled') {
                    // برای ارسال زمان‌دار، بررسی زمان
                    if ($autoSms->scheduled_at && $autoSms->scheduled_at->isFuture()) {
                        continue; // هنوز زمان نرسیده
                    }
                }

                // بررسی اینکه آیا قبلاً ارسال شده یا نه (برای immediate)
                if ($autoSms->send_type === 'immediate' && $autoSms->last_sent_at) {
                    // اگر قبلاً ارسال شده، بررسی کنیم که آیا شرط دوباره برقرار شده یا نه
                    // اینجا می‌توانیم منطق پیچیده‌تری پیاده کنیم
                }

                // دریافت اقامت‌گرانی که شرط‌ها را برآورده می‌کنند
                $matchingResidents = $this->getMatchingResidents($autoSms);

                if ($matchingResidents->isEmpty()) {
                    // به‌روزرسانی last_checked_at
                    $autoSms->update(['last_checked_at' => now()]);
                    continue;
                }

                // ارسال پیامک به اقامت‌گران
                $this->sendSmsToResidents($autoSms, $matchingResidents);

                // به‌روزرسانی اطلاعات
                $autoSms->update([
                    'last_checked_at' => now(),
                    'last_sent_at' => now(),
                    'total_sent' => $autoSms->total_sent + $matchingResidents->count(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in CheckAndSendAutoSms job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * دریافت اقامت‌گرانی که شرط‌ها را برآورده می‌کنند
     */
    protected function getMatchingResidents(AutoSms $autoSms)
    {
        $conditions = $autoSms->conditions;
        
        if ($conditions->isEmpty()) {
            return Resident::all();
        }

        $residents = Resident::all();
        $matchingResidents = collect([]);

        foreach ($residents as $resident) {
            if ($autoSms->checkConditions($resident->id)) {
                $matchingResidents->push($resident);
            }
        }

        return $matchingResidents;
    }

    /**
     * ارسال پیامک به اقامت‌گران
     */
    protected function sendSmsToResidents(AutoSms $autoSms, $residents)
    {
        $melipayamakService = new MelipayamakService();
        
        // بررسی اینکه pattern وجود دارد
        if (!$autoSms->pattern || !$autoSms->pattern->pattern_code) {
            Log::error('Auto SMS pattern not found or pattern_code missing', [
                'auto_sms_id' => $autoSms->id,
                'pattern_id' => $autoSms->pattern_id,
            ]);
            return;
        }

        $pattern = $autoSms->pattern;

        foreach ($residents as $resident) {
            if (empty($resident->phone)) {
                continue;
            }

            try {
                // تبدیل Resident به فرمت مورد نیاز برای extractPatternVariables
                $residentArray = [
                    'id' => $resident->resident_id ?? $resident->id,
                    'name' => $resident->full_name,
                    'phone' => $resident->phone,
                    'unit_id' => $resident->unit_id ?? null,
                    'unit_name' => $resident->unit_name ?? '',
                    'room_id' => $resident->room_id ?? null,
                    'room_name' => $resident->room_name ?? '',
                    'bed_id' => $resident->bed_id ?? null,
                    'bed_name' => $resident->bed_name ?? '',
                ];

                // استخراج متغیرها از الگو
                $variables = $this->extractPatternVariables($pattern->text, $residentArray);

                // ایجاد رکورد در sms_message_residents
                $smsMessageResident = SmsMessageResident::create([
                    'sms_message_id' => null, // برای پیام‌های الگویی sms_message_id نداریم
                    'pattern_id' => $pattern->id,
                    'is_pattern' => true,
                    'pattern_variables' => implode(';', $variables),
                    'resident_id' => $resident->id,
                    'resident_name' => $resident->full_name,
                    'phone' => $resident->phone,
                    'title' => $autoSms->title,
                    'description' => $pattern->text,
                    'status' => 'pending',
                ]);

                // ارسال پیامک با الگو
                $bodyId = (int)$pattern->pattern_code;
                $result = $melipayamakService->sendByBaseNumber(
                    $resident->phone,
                    $bodyId,
                    $variables, // آرایه متغیرها
                    null, // شماره فرستنده (از config استفاده می‌شود)
                    null // API Key (از config استفاده می‌شود)
                );

                if ($result['success']) {
                    $smsMessageResident->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'response_code' => $result['response_code'] ?? null,
                    ]);
                } else {
                    $smsMessageResident->update([
                        'status' => 'failed',
                        'error_message' => $result['message'] ?? 'خطا در ارسال',
                        'response_code' => $result['response_code'] ?? null,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error sending auto SMS to resident', [
                    'resident_id' => $resident->id,
                    'auto_sms_id' => $autoSms->id,
                    'pattern_id' => $pattern->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * استخراج متغیرها از متن الگو
     */
    protected function extractPatternVariables($patternText, $resident)
    {
        // پیدا کردن تمام متغیرها در الگو (مثل {0}, {1}, {2})
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[1])) {
            return []; // اگر متغیری وجود نداشت
        }

        // دریافت اطلاعات کامل resident
        $residentData = $this->getResidentData($resident);

        // بارگذاری متغیرها از دیتابیس
        $variables = PatternVariable::where('is_active', true)
            ->get()
            ->keyBy('code'); // کلید بر اساس کد (مثل {0}, {1})

        $result = [];
        $usedIndices = array_unique(array_map('intval', $matches[1]));
        sort($usedIndices); // مرتب‌سازی بر اساس ترتیب در الگو

        foreach ($usedIndices as $index) {
            $code = '{' . $index . '}';
            $variable = $variables->get($code);

            if ($variable) {
                $value = $this->getVariableValue($variable, $residentData, null);
                
                // اطمینان از اینکه value یک رشته است
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                
                // اگر مقدار خالی است، مقدار خالی بگذار
                if (empty(trim($value))) {
                    $value = '';
                }
                
                $result[] = $value;
            } else {
                // اگر متغیر در دیتابیس پیدا نشد، مقدار خالی
                $result[] = '';
            }
        }

        return $result;
    }

    /**
     * دریافت اطلاعات کامل resident
     */
    protected function getResidentData($resident)
    {
        try {
            $residentService = new ResidentService();
            $data = $residentService->getResidentById($resident['id']); // resident_id از API
            
            if ($data) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Error getting resident data in CheckAndSendAutoSms', [
                'resident_id' => $resident['id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        // در صورت خطا، از داده‌های موجود استفاده می‌کنیم
        return [
            'resident' => [
                'id' => $resident['id'] ?? null,
                'full_name' => $resident['name'] ?? '',
                'name' => $resident['name'] ?? '',
                'phone' => $resident['phone'] ?? '',
            ],
            'unit' => [
                'id' => $resident['unit_id'] ?? null,
                'name' => $resident['unit_name'] ?? '',
            ],
            'room' => [
                'id' => $resident['room_id'] ?? null,
                'name' => $resident['room_name'] ?? '',
            ],
            'bed' => [
                'id' => $resident['bed_id'] ?? null,
                'name' => $resident['bed_name'] ?? '',
            ],
        ];
    }

    /**
     * دریافت مقدار متغیر بر اساس فیلد جدول
     */
    protected function getVariableValue($variable, $residentData, $reportData = null)
    {
        $field = $variable->table_field ?? '';
        $type = $variable->variable_type ?? 'user';
        
        if ($type === 'user') {
            // فیلدهای کاربر
            if (strpos($field, 'unit_') === 0) {
                $key = substr($field, 5); // حذف 'unit_' از ابتدا
                $value = $residentData['unit'][$key] ?? '';
                return is_string($value) ? $value : (string)$value;
            } elseif (strpos($field, 'room_') === 0) {
                $key = substr($field, 5); // حذف 'room_' از ابتدا
                $value = $residentData['room'][$key] ?? '';
                return is_string($value) ? $value : (string)$value;
            } elseif (strpos($field, 'bed_') === 0) {
                $key = substr($field, 4); // حذف 'bed_' از ابتدا
                $value = $residentData['bed'][$key] ?? '';
                return is_string($value) ? $value : (string)$value;
            } else {
                // فیلدهای مستقیم resident
                $value = $residentData['resident'][$field] ?? '';
                return is_string($value) ? $value : (string)$value;
            }
        } elseif ($type === 'report' && $reportData) {
            // فیلدهای گزارش
            $value = $reportData[$field] ?? '';
            return is_string($value) ? $value : (string)$value;
        }
        
        return '';
    }
}

