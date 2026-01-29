<?php

namespace App\Services;

use App\Models\Resident;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Pattern;
use App\Models\Settings;
use App\Services\MelipayamakService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WelcomeMessageService
{
    protected $melipayamakService;

    public function __construct(MelipayamakService $melipayamakService)
    {
        $this->melipayamakService = $melipayamakService;
    }

    /**
     * بررسی و ارسال پیام‌های خوش‌آمدگویی
     */
    public function processWelcomeMessages()
    {
        try {
            $settings = Settings::first();
            
            if (!$settings || !$settings->welcome_system_active) {
                Log::info('Welcome message system is not active');
                return;
            }

            $welcomeStartDate = $settings->welcome_start_date;
            if (!$welcomeStartDate) {
                Log::info('Welcome start date is not set');
                return;
            }

            // دریافت گزارش خوش‌آمدگویی
            $welcomeReport = Report::find($settings->welcome_report_id);
            if (!$welcomeReport) {
                Log::info('Welcome report is not set');
                return;
            }

            // دریافت اقامت‌گران واجد شرایط با فیلترهای کدنویسی شده
            $residents = $this->getEligibleResidents($welcomeStartDate);
            
            foreach ($residents as $resident) {
                // بررسی اینکه آیا قبلاً پیام دریافت کرده است
                if ($this->hasResidentReceivedMessage($resident->id)) {
                    continue;
                }

                // ایجاد گزارش اقامت‌گر
                $residentReport = $this->createResidentReport($resident, $welcomeReport);
                
                if ($residentReport) {
                    // ارسال پیام خوش‌آمدگویی
                    $this->sendWelcomeMessage($resident, $residentReport);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error processing welcome messages: ' . $e->getMessage());
        }
    }

    /**
     * دریافت اقامت‌گران واجد شرایط با فیلترهای کدنویسی شده
     */
    private function getEligibleResidents($welcomeStartDate)
    {
        $query = Resident::query();

        // فیلتر 1: فقط اقامت‌گرانی که وضعیت قرارداد آنها برابر "active" باشد
        $query->where('contract_state', '=', 'active');

        // فیلتر 2: تاریخ ثبت‌نام یا شروع قرارداد از تاریخ شروع انتخاب شده به بعد باشد
        $query->where(function($q) use ($welcomeStartDate) {
            $q->where('contract_start_date', '>=', $welcomeStartDate)
              ->orWhere('contract_created_at', '>=', $welcomeStartDate)
              ->orWhere('resident_created_at', '>=', $welcomeStartDate)
              ->orWhere('created_at', '>=', $welcomeStartDate);
        });

        // فیلتر 3: شماره تلفن معتبر داشته باشند
        $query->where(function($q) {
            $q->whereNotNull('resident_phone')
              ->orWhere('resident_phone', '!=', '')
              ->orWhereNotNull('phone')
              ->orWhere('phone', '!=', '');
        });

        // فیلتر 4: حذف کسانی که قبلاً پیام دریافت کرده‌اند
        $sentResidentIds = $this->getSentResidentIds();
        if (!empty($sentResidentIds)) {
            $query->whereNotIn('id', $sentResidentIds);
        }

        return $query->get();
    }

    /**
     * دریافت لیست اقامت‌گرانی که قبلاً پیام خوش‌آمدگویی دریافت کرده‌اند
     */
    private function getSentResidentIds()
    {
        try {
            // بررسی در جدول resident_reports که پیام خوش‌آمدگویی برایشان ارسال شده
            $sentReports = ResidentReport::whereHas('report', function($q) {
                $q->where('title', 'like', '%خوش‌آمدگویی%')
                  ->orWhere('title', 'like', '%welcome%');
            })->where('sms_sent', true)->pluck('resident_id')->toArray();

            return $sentReports;
        } catch (\Exception $e) {
            Log::error('Error getting sent resident IDs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * بررسی اینکه آیا اقامت‌گر قبلاً پیام دریافت کرده است
     */
    private function hasResidentReceivedMessage($residentId)
    {
        try {
            return ResidentReport::where('resident_id', $residentId)
                ->whereHas('report', function($q) {
                    $q->where('title', 'like', '%خوش‌آمدگویی%')
                      ->orWhere('title', 'like', '%welcome%');
                })
                ->where('sms_sent', true)
                ->exists();
        } catch (\Exception $e) {
            Log::error('Error checking if resident received message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ایجاد گزارش اقامت‌گر
     */
    private function createResidentReport($resident, $welcomeReport)
    {
        try {
            return ResidentReport::create([
                'report_id' => $welcomeReport->id,
                'resident_id' => $resident->id,
                'resident_name' => $resident->resident_full_name ?? $resident->name ?? '',
                'resident_phone' => $resident->resident_phone ?? $resident->phone ?? '',
                'sms_sent' => false,
                'has_been_sent' => false,
                'is_checked' => false,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating resident report: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ارسال پیام خوش‌آمدگویی
     */
    private function sendWelcomeMessage($resident, $residentReport)
    {
        try {
            // دریافت الگوی پیام خوش‌آمدگویی
            $pattern = Pattern::find($residentReport->report->pattern_id ?? null);
            
            if (!$pattern) {
                // اگر الگو وجود نداشت، از روش قدیمی استفاده می‌کنیم
                $patternCode = 'welcome';
                $message = $this->prepareWelcomeMessage($resident);
                
                $result = $this->melipayamakService->sendPattern(
                    $patternCode,
                    $residentReport->resident_phone,
                    [
                        'name' => $residentReport->resident_name,
                    ]
                );
            } else {
                // استفاده از متغیرهای اختصاصی الگو
                $message = $pattern->replaceVariables($pattern->text, $this->prepareResidentData($resident));
                
                $result = $this->melipayamakService->sendPattern(
                    $pattern->pattern_code,
                    $residentReport->resident_phone,
                    $this->preparePatternVariables($pattern, $resident)
                );
            }

            if ($result && isset($result['success']) && $result['success']) {
                // به‌روزرسانی گزارش اقامت‌گر
                $residentReport->update([
                    'sms_sent' => true,
                    'has_been_sent' => true,
                ]);

                Log::info("Welcome message sent successfully to resident {$resident->id}");
            } else {
                Log::error("Failed to send welcome message to resident {$resident->id}: " . json_encode($result));
            }

        } catch (\Exception $e) {
            Log::error('Error sending welcome message: ' . $e->getMessage());
        }
    }
    
    /**
     * آماده‌سازی داده‌های اقامت‌گر برای جایگزینی متغیرها
     */
    private function prepareResidentData($resident)
    {
        return [
            'full_name' => $resident->resident_full_name ?? $resident->name ?? '',
            'name' => $resident->resident_full_name ?? $resident->name ?? '',
            'phone' => $resident->resident_phone ?? $resident->phone ?? '',
            'national_id' => $resident->national_id ?? '',
            'national_code' => $resident->national_code ?? $resident->national_id ?? '',
            'unit_name' => $resident->unit_name ?? '',
            'unit_code' => $resident->unit_code ?? '',
            'room_name' => $resident->room_name ?? '',
            'bed_name' => $resident->bed_name ?? '',
            'start_date' => $resident->contract_start_date ?? $resident->start_date ?? '',
            'end_date' => $resident->contract_end_date ?? $resident->end_date ?? '',
            'contract_start_date' => $resident->contract_start_date ?? '',
            'contract_end_date' => $resident->contract_end_date ?? '',
            'expiry_date' => $resident->contract_expiry_date ?? $resident->expiry_date ?? '',
        ];
    }
    
    /**
     * آماده‌سازی متغیرهای الگو برای ارسال به ملی پیامک
     */
    private function preparePatternVariables($pattern, $resident)
    {
        $variables = $pattern->getPatternVariablesWithCodes();
        $residentData = $this->prepareResidentData($resident);
        $patternVariables = [];
        
        foreach ($variables as $code => $variable) {
            $value = $this->getVariableValue($variable, $residentData);
            $patternVariables[$variable->table_field] = $value;
        }
        
        return $patternVariables;
    }
    
    /**
     * دریافت مقدار متغیر
     */
    private function getVariableValue($variable, $data = [])
    {
        $tableField = $variable->table_field;
        
        // اگر داده مستقیم ارسال شده باشد
        if (isset($data[$tableField])) {
            return $data[$tableField];
        }
        
        // اگر داده تو در تو باشد (مثل category.name)
        if (strpos($tableField, '.') !== false) {
            $parts = explode('.', $tableField);
            $value = $data;
            foreach ($parts as $part) {
                if (isset($value[$part])) {
                    $value = $value[$part];
                } else {
                    return '[' . $tableField . ']';
                }
            }
            return $value;
        }
        
        return '[' . $tableField . ']';
    }

    /**
     * آماده‌سازی پیام خوش‌آمدگویی
     */
    private function prepareWelcomeMessage($resident)
    {
        $name = $resident->resident_full_name ?? $resident->name ?? 'عزیز';
        
        return "خوش آمدید {$name}! امیدواریم اقامت خوبی در مجموعه ما داشته باشید.";
    }
}
