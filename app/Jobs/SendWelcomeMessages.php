<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\Settings;
use App\Models\Resident;
use App\Models\Pattern;
use App\Models\SmsMessageResident;
use App\Models\SenderNumber;
use App\Models\ApiKey;
use App\Services\MelipayamakService;
use App\Services\ResidentService;
use App\Models\PatternVariable;
use Carbon\Carbon;

class SendWelcomeMessages implements ShouldQueue
{
    use Queueable;

    public $tries = 1;
    public $timeout = 300;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $settings = Settings::getSettings();
            
            // بررسی اینکه آیا تنظیمات خوش‌آمدگویی فعال است
            if (!$settings->welcome_pattern_id || !$settings->welcome_start_datetime) {
                Log::info('Welcome messages not configured');
                return;
            }

            $pattern = Pattern::find($settings->welcome_pattern_id);
            if (!$pattern || !$pattern->is_active) {
                Log::warning('Welcome pattern not found or inactive', [
                    'pattern_id' => $settings->welcome_pattern_id
                ]);
                return;
            }

            $startDateTime = Carbon::parse($settings->welcome_start_datetime);
            
            // پیدا کردن اقامت‌گرانی که بعد از تاریخ شروع ایجاد شده‌اند
            // استفاده از resident_created_at که تاریخ ایجاد اقامت‌گر در سیستم اصلی است
            $residents = Resident::whereNotNull('resident_created_at')
                ->where('resident_created_at', '>=', $startDateTime)
                ->whereNotNull('resident_phone')
                ->where('resident_phone', '!=', '')
                ->get();

            Log::info('Checking welcome messages', [
                'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                'residents_count' => $residents->count()
            ]);

            $sentCount = 0;
            $skippedCount = 0;
            $errorCount = 0;

            foreach ($residents as $resident) {
                // بررسی اینکه آیا قبلاً پیام خوش‌آمدگویی برای این اقامت‌گر ارسال شده است
                $existingMessage = SmsMessageResident::where('resident_id', $resident->id)
                    ->where('pattern_id', $pattern->id)
                    ->where('status', 'sent')
                    ->first();

                if ($existingMessage) {
                    $skippedCount++;
                    continue;
                }

                try {
                    // استخراج متغیرها
                    $variables = $this->extractPatternVariables($pattern->text, $resident);
                    
                    // دریافت شماره فرستنده
                    $senderNumber = SenderNumber::getActivePatternNumbers()->first();
                    $senderNumberValue = $senderNumber ? $senderNumber->number : null;
                    $apiKey = $senderNumber && !empty($senderNumber->api_key) ? $senderNumber->api_key : null;

                    // اگر API Key از sender number دریافت نشد، از جدول api_keys استفاده می‌کنیم
                    if (empty($apiKey)) {
                        $dbConsoleKey = ApiKey::getKeyValue('console_api_key');
                        $dbApiKey = ApiKey::getKeyValue('api_key');
                        $configConsoleKey = config('services.melipayamak.console_api_key');
                        $configApiKey = config('services.melipayamak.api_key');
                        
                        $apiKey = $dbConsoleKey
                            ?: $dbApiKey
                            ?: $configConsoleKey
                            ?: $configApiKey;
                    }

                    // ایجاد رکورد در sms_message_residents
                    $smsMessageResident = SmsMessageResident::create([
                        'sms_message_id' => null,
                        'report_id' => null,
                        'pattern_id' => $pattern->id,
                        'is_pattern' => true,
                        'pattern_variables' => implode(';', $variables),
                        'resident_id' => $resident->id,
                        'resident_name' => $resident->resident_full_name ?? '',
                        'phone' => $resident->resident_phone,
                        'title' => $pattern->title,
                        'description' => $pattern->text,
                        'status' => 'pending',
                    ]);

                    // ارسال پیامک با الگو
                    $melipayamakService = new MelipayamakService();
                    $bodyId = (int)$pattern->pattern_code;
                    
                    $result = $melipayamakService->sendByBaseNumber(
                        $resident->resident_phone,
                        $bodyId,
                        $variables,
                        $senderNumberValue,
                        $apiKey
                    );

                    // به‌روزرسانی وضعیت
                    if ($result['success']) {
                        $smsMessageResident->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'response_code' => $result['response_code'] ?? null,
                            'error_message' => null,
                        ]);
                        $sentCount++;
                    } else {
                        $smsMessageResident->update([
                            'status' => 'failed',
                            'error_message' => $result['message'] ?? 'خطا در ارسال',
                        ]);
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending welcome message', [
                        'resident_id' => $resident->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $errorCount++;
                }
            }

            Log::info('Welcome messages job completed', [
                'sent' => $sentCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in SendWelcomeMessages job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * استخراج متغیرها از متن الگو
     */
    protected function extractPatternVariables($patternText, $resident)
    {
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[1])) {
            return [];
        }

        $residentData = $this->getResidentData($resident);

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
                $value = $this->getVariableValue($variable, $residentData);
                $result[] = $value;
            } else {
                $result[] = '';
            }
        }

        return $result;
    }

    /**
     * دریافت داده‌های اقامت‌گر
     */
    protected function getResidentData($resident)
    {
        try {
            $residentService = new ResidentService();
            $data = $residentService->getResidentById($resident->resident_id);
            
            if ($data) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Error getting resident data', [
                'resident_id' => $resident->resident_id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback به داده‌های دیتابیس
        return [
            'resident' => [
                'id' => $resident->resident_id,
                'full_name' => $resident->resident_full_name ?? '',
                'phone' => $resident->resident_phone ?? '',
                'age' => $resident->resident_age ?? '',
                'birth_date' => $resident->resident_birth_date ?? '',
                'job' => $resident->resident_job ?? '',
            ],
            'unit' => [
                'name' => $resident->unit_name ?? '',
                'code' => $resident->unit_code ?? '',
            ],
            'room' => [
                'name' => $resident->room_name ?? '',
                'code' => $resident->room_code ?? '',
            ],
            'bed' => [
                'name' => $resident->bed_name ?? '',
                'code' => $resident->bed_code ?? '',
            ],
        ];
    }

    /**
     * دریافت مقدار متغیر
     */
    protected function getVariableValue($variable, $residentData)
    {
        $field = $variable->table_field ?? $variable->field ?? '';
        $type = $variable->variable_type ?? 'user';
        
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
                $date = Carbon::parse($date);
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
