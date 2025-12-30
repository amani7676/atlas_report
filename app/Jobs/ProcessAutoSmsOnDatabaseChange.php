<?php

namespace App\Jobs;

use App\Models\AutoSms;
use App\Models\AutoSmsCondition;
use App\Models\Resident;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\SmsMessageResident;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\TableName;
use App\Services\MelipayamakService;
use App\Services\ResidentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ProcessAutoSmsOnDatabaseChange implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 1;
    public $timeout = 600;

    public $autoSms;
    public $event;

    /**
     * Create a new job instance.
     */
    public function __construct($autoSms, $event = null)
    {
        $this->autoSms = is_object($autoSms) ? $autoSms : AutoSms::find($autoSms);
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->autoSms) {
                return;
            }

            // اگر event وجود دارد، از متد جدید استفاده می‌کنیم
            if ($this->event) {
                $this->processAutoSmsWithEvent($this->autoSms, $this->event);
            } else {
                // برای سازگاری با کد قدیمی (اگر event نبود)
                $this->processAutoSms($this->autoSms);
            }
        } catch (\Exception $e) {
            Log::error('Error in ProcessAutoSmsOnDatabaseChange job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * پردازش یک auto_sms با event
     */
    protected function processAutoSmsWithEvent(AutoSms $autoSms, $event)
    {
        try {
            // بررسی نوع ارسال
            if ($autoSms->send_type === 'scheduled') {
                // برای ارسال زمان‌دار، بررسی زمان
                if ($autoSms->scheduled_at && $autoSms->scheduled_at->isFuture()) {
                    return; // هنوز زمان نرسیده
                }
                
                // بررسی اینکه آیا امروز چک شده یا نه (برای scheduled فقط یک بار در روز)
                if ($autoSms->last_checked_at && $autoSms->last_checked_at->isToday()) {
                    return; // امروز قبلاً چک شده
                }
            }

            // دریافت resident_id از event
            $residentId = $event->residentId;
            if (!$residentId) {
                return;
            }

            $resident = Resident::find($residentId);
            if (!$resident) {
                return;
            }

            // ساخت recordData از event
            $recordData = [
                'table' => $event->tableName,
                'record' => $event->model,
                'action' => $event->action,
                'original_data' => $event->originalData,
                'changed_data' => $event->changedData,
                'resident_id' => $residentId,
            ];

            // پردازش رکورد
            $this->processChangedRecord($autoSms, $recordData, [$event->tableName]);

            // به‌روزرسانی اطلاعات
            $autoSms->update([
                'last_checked_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing auto SMS', [
                'auto_sms_id' => $autoSms->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * پردازش یک auto_sms (برای سازگاری با کد قدیمی)
     */
    protected function processAutoSms(AutoSms $autoSms)
    {
        try {
            // بررسی نوع ارسال
            if ($autoSms->send_type === 'scheduled') {
                // برای ارسال زمان‌دار، بررسی زمان
                if ($autoSms->scheduled_at && $autoSms->scheduled_at->isFuture()) {
                    return; // هنوز زمان نرسیده
                }
                
                // بررسی اینکه آیا امروز چک شده یا نه (برای scheduled فقط یک بار در روز)
                if ($autoSms->last_checked_at && $autoSms->last_checked_at->isToday()) {
                    return; // امروز قبلاً چک شده
                }
            }

            // دریافت جداول مربوطه
            $relatedTables = $autoSms->related_tables ?? [];
            if (empty($relatedTables)) {
                return;
            }

            // دریافت تغییرات در جداول
            $changedRecords = $this->getChangedRecords($relatedTables, $autoSms);

            if (empty($changedRecords)) {
                // به‌روزرسانی last_checked_at
                $autoSms->update(['last_checked_at' => now()]);
                return;
            }

            // پردازش هر رکورد تغییر یافته
            foreach ($changedRecords as $record) {
                $this->processChangedRecord($autoSms, $record, $relatedTables);
            }

            // به‌روزرسانی اطلاعات
            $autoSms->update([
                'last_checked_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing auto SMS', [
                'auto_sms_id' => $autoSms->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * دریافت رکوردهای تغییر یافته در جداول
     */
    protected function getChangedRecords(array $relatedTables, AutoSms $autoSms)
    {
        $changedRecords = [];

        foreach ($relatedTables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            // دریافت رکوردهای تغییر یافته امروز یا از آخرین چک
            $lastCheckedAt = $autoSms->last_checked_at ?? Carbon::today()->subDays(7);
            
            $query = DB::table($tableName)
                ->where(function ($q) use ($lastCheckedAt) {
                    $q->where('created_at', '>=', $lastCheckedAt)
                      ->orWhere('updated_at', '>=', $lastCheckedAt);
                });

            // اگر جدول residents است، باید resident_id را بگیریم
            if ($tableName === 'residents') {
                $records = $query->get();
                foreach ($records as $record) {
                    $changedRecords[] = [
                        'table' => $tableName,
                        'record' => $record,
                        'resident_id' => $record->id ?? $record->resident_id ?? null,
                    ];
                }
            } else {
                // برای جداول دیگر، باید resident_id را از رابطه پیدا کنیم
                $records = $query->get();
                foreach ($records as $record) {
                    $residentId = $this->getResidentIdFromRecord($tableName, $record);
                    if ($residentId) {
                        $changedRecords[] = [
                            'table' => $tableName,
                            'record' => $record,
                            'resident_id' => $residentId,
                        ];
                    }
                }
            }
        }

        return $changedRecords;
    }

    /**
     * دریافت resident_id از رکورد
     */
    protected function getResidentIdFromRecord($tableName, $record)
    {
        // بررسی فیلدهای معمول برای resident_id
        if (isset($record->resident_id)) {
            return $record->resident_id;
        }

        // برای resident_reports
        if ($tableName === 'resident_reports' && isset($record->resident_id)) {
            return $record->resident_id;
        }

        // برای سایر جداول، باید رابطه را بررسی کنیم
        // در حال حاضر فقط جداول خاص را پشتیبانی می‌کنیم
        return null;
    }

    /**
     * پردازش یک رکورد تغییر یافته
     */
    protected function processChangedRecord(AutoSms $autoSms, array $recordData, array $relatedTables)
    {
        try {
            $residentId = $recordData['resident_id'] ?? null;
            if (!$residentId) {
                return;
            }

            $resident = Resident::find($residentId);
            if (!$resident) {
                return;
            }

            // دریافت شرط‌ها بر اساس نوع
            $interConditions = $autoSms->conditions()->where('condition_type', 'inter')->get();
            $checkConditions = $autoSms->conditions()->where('condition_type', 'check')->get();
            $changeConditions = $autoSms->conditions()->where('condition_type', 'change')->get();

            // ارزیابی شرط‌های inter (ورود)
            if (!$interConditions->isEmpty()) {
                $interResult = $this->evaluateConditions($interConditions, $residentId, $recordData);
                if (!$interResult) {
                    return; // شرط ورود برقرار نیست
                }
            }

            // ارزیابی شرط‌های check (اما اگر)
            if (!$checkConditions->isEmpty()) {
                $checkResult = $this->evaluateConditions($checkConditions, $residentId, $recordData);
                if (!$checkResult) {
                    return; // شرط چک برقرار نیست
                }
            }

            // بررسی تکراری بودن (در همان روز)
            if ($this->isDuplicate($autoSms, $residentId)) {
                return;
            }

            // ارزیابی شرط‌های change (تغییرات)
            // این شرط‌ها بعد از ایجاد گزارش/پیامک بررسی می‌شوند
            // اما برای اطمینان، ابتدا بررسی می‌کنیم که آیا باید گزارش ایجاد شود یا نه
            $shouldCreateReport = $this->shouldCreateReport($autoSms, $residentId, $recordData);

            if ($shouldCreateReport) {
                // ایجاد گزارش
                $report = $this->createReport($autoSms, $residentId, $recordData);
                
                if ($report) {
                    // ارزیابی شرط‌های change با داده‌های گزارش ایجاد شده
                    if (!$changeConditions->isEmpty()) {
                        $changeResult = $this->evaluateChangeConditions($changeConditions, $residentId, $report, $recordData);
                        if (!$changeResult) {
                            // اگر شرط change برقرار نبود، گزارش را حذف می‌کنیم
                            $report->delete();
                            return;
                        }
                    }

                    // ارسال پیامک
                    $this->sendSms($autoSms, $resident, $report, $recordData);
                }
            } else {
                // فقط ارسال پیامک بدون ایجاد گزارش
                $this->sendSms($autoSms, $resident, null, $recordData);
            }
        } catch (\Exception $e) {
            Log::error('Error processing changed record', [
                'auto_sms_id' => $autoSms->id,
                'record_data' => $recordData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * ارزیابی شرط‌ها
     */
    protected function evaluateConditions($conditions, $residentId, $recordData)
    {
        $result = null;
        $lastLogicalOperator = null;

        foreach ($conditions as $condition) {
            $conditionResult = $this->evaluateCondition($condition, $residentId, $recordData);

            if ($result === null) {
                $result = $conditionResult;
            } else {
                if ($lastLogicalOperator === 'AND') {
                    $result = $result && $conditionResult;
                } else { // OR
                    $result = $result || $conditionResult;
                }
            }

            $lastLogicalOperator = $condition->logical_operator ?? 'AND';
        }

        return $result ?? true;
    }

    /**
     * ارزیابی یک شرط
     */
    protected function evaluateCondition(AutoSmsCondition $condition, $residentId, $recordData)
    {
        try {
            // استفاده از متد evaluate موجود در مدل
            $result = $condition->evaluate($residentId);
            
            // اگر شرط بر روی جدول تغییر یافته است، باید با داده‌های جدید بررسی شود
            if ($condition->field_type === $recordData['table']) {
                $fieldValue = $recordData['record']->{$condition->field_name} ?? null;
                if ($fieldValue !== null) {
                    // مقایسه مستقیم با مقدار شرط
                    return $this->compareValue($fieldValue, $condition->value, $condition->operator, $condition->data_type);
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error evaluating condition', [
                'condition_id' => $condition->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * مقایسه دو مقدار
     */
    protected function compareValue($fieldValue, $compareValue, $operator, $dataType)
    {
        if ($fieldValue === null) {
            return false;
        }

        // تبدیل مقادیر بر اساس نوع داده
        if ($dataType === 'number') {
            $fieldValue = is_numeric($fieldValue) ? (float)$fieldValue : 0;
            $compareValue = is_numeric($compareValue) ? (float)$compareValue : 0;
        } elseif ($dataType === 'date') {
            try {
                if ($fieldValue instanceof Carbon) {
                    $fieldDate = $fieldValue;
                } elseif (is_string($fieldValue)) {
                    $fieldDate = Carbon::parse($fieldValue);
                } else {
                    return false;
                }

                if ($operator === 'days_after' || $operator === 'days_before') {
                    $days = (int)$compareValue;
                    $today = Carbon::today();
                    
                    if ($operator === 'days_after') {
                        return $fieldDate->copy()->addDays($days)->lte($today);
                    } else {
                        return $fieldDate->copy()->subDays($days)->gte($today);
                    }
                }

                $days = (int)$compareValue;
                $targetDate = Carbon::today()->addDays($days);
                $fieldValue = $fieldDate->format('Y-m-d');
                $compareValue = $targetDate->format('Y-m-d');
            } catch (\Exception $e) {
                return false;
            }
        } elseif ($dataType === 'boolean') {
            $fieldValue = (bool)$fieldValue;
            $compareValue = filter_var($compareValue, FILTER_VALIDATE_BOOLEAN);
        } else {
            $fieldValue = (string)$fieldValue;
            $compareValue = (string)$compareValue;
        }

        switch ($operator) {
            case '>':
                return $dataType === 'number' ? $fieldValue > $compareValue : false;
            case '<':
                return $dataType === 'number' ? $fieldValue < $compareValue : false;
            case '=':
                return $fieldValue === $compareValue;
            case '>=':
                return $dataType === 'number' ? $fieldValue >= $compareValue : false;
            case '<=':
                return $dataType === 'number' ? $fieldValue <= $compareValue : false;
            case '!=':
                return $fieldValue !== $compareValue;
            case 'contains':
                return $dataType === 'string' && stripos((string)$fieldValue, (string)$compareValue) !== false;
            case 'not_contains':
                return $dataType === 'string' && stripos((string)$fieldValue, (string)$compareValue) === false;
            default:
                return false;
        }
    }

    /**
     * بررسی تکراری بودن
     */
    protected function isDuplicate(AutoSms $autoSms, $residentId)
    {
        // بررسی اینکه آیا امروز برای این auto_sms و resident_id گزارش یا پیامک ایجاد شده یا نه
        $today = Carbon::today();
        
        // بررسی در sms_message_residents بر اساس pattern_id و title
        $existingSms = SmsMessageResident::where('resident_id', $residentId)
            ->where(function ($q) use ($autoSms) {
                if ($autoSms->pattern_id) {
                    $q->where('pattern_id', $autoSms->pattern_id);
                }
                if ($autoSms->title) {
                    $q->orWhere('title', $autoSms->title);
                }
            })
            ->whereDate('created_at', $today)
            ->first();

        if ($existingSms) {
            return true;
        }

        // بررسی در resident_reports بر اساس report_id (اگر auto_sms دارای report_id باشد)
        // در حال حاضر، از اولین report با auto_ability استفاده می‌کنیم
        $report = Report::where('auto_ability', true)->first();
        if ($report) {
            $existingReport = ResidentReport::where('resident_id', $residentId)
                ->where('report_id', $report->id)
                ->whereDate('created_at', $today)
                ->first();

            if ($existingReport) {
                return true;
            }
        }

        return false;
    }

    /**
     * بررسی اینکه آیا باید گزارش ایجاد شود یا نه
     */
    protected function shouldCreateReport(AutoSms $autoSms, $residentId, $recordData)
    {
        // اگر auto_sms دارای report_id است، باید گزارش ایجاد شود
        // در حال حاضر، همیشه true برمی‌گردانیم تا گزارش ایجاد شود
        // می‌توانیم بعداً این منطق را پیچیده‌تر کنیم (مثلاً اضافه کردن فیلد report_id به auto_sms)
        return true;
    }

    /**
     * ایجاد گزارش
     */
    protected function createReport(AutoSms $autoSms, $residentId, $recordData)
    {
        try {
            // پیدا کردن report مناسب
            // در حال حاضر، از اولین report با auto_ability استفاده می‌کنیم
            $report = Report::where('auto_ability', true)->first();
            
            if (!$report) {
                Log::warning('No report with auto_ability found', [
                    'auto_sms_id' => $autoSms->id,
                ]);
                return null;
            }

            // ایجاد resident_report
            $residentReport = ResidentReport::create([
                'report_id' => $report->id,
                'resident_id' => $residentId,
                'notes' => 'ایجاد شده به صورت خودکار از سیستم ارسال خودکار',
                'has_been_sent' => false,
            ]);

            return $residentReport;
        } catch (\Exception $e) {
            Log::error('Error creating report', [
                'auto_sms_id' => $autoSms->id,
                'resident_id' => $residentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * ارزیابی شرط‌های change
     */
    protected function evaluateChangeConditions($conditions, $residentId, $report, $recordData)
    {
        // ارزیابی شرط‌های change با داده‌های گزارش ایجاد شده
        foreach ($conditions as $condition) {
            // بررسی اینکه آیا فیلد شرط با فیلدهای گزارش مطابقت دارد
            $reportValue = $this->getReportFieldValue($report, $condition->field_name);
            $conditionResult = $this->compareValue($reportValue, $condition->value, $condition->operator, $condition->data_type);
            
            if (!$conditionResult) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * دریافت مقدار فیلد از گزارش
     */
    protected function getReportFieldValue($report, $fieldName)
    {
        // اگر report یک ResidentReport است
        if ($report instanceof ResidentReport) {
            $reportModel = $report->report;
            if ($reportModel) {
                return $reportModel->{$fieldName} ?? null;
            }
        }
        
        return null;
    }

    /**
     * ارسال پیامک
     */
    protected function sendSms(AutoSms $autoSms, Resident $resident, $report = null, $recordData = [])
    {
        try {
            $melipayamakService = new MelipayamakService();
            
            if (!$autoSms->pattern || !$autoSms->pattern->pattern_code) {
                Log::error('Auto SMS pattern not found', [
                    'auto_sms_id' => $autoSms->id,
                ]);
                return;
            }

            $pattern = $autoSms->pattern;

            if (empty($resident->phone)) {
                return;
            }

            // استخراج متغیرها از الگو
            $variables = $this->extractPatternVariables($pattern->text, $resident, $report, $recordData);

            // ایجاد رکورد در sms_message_residents
            $smsMessageResident = SmsMessageResident::create([
                'sms_message_id' => null,
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
                $variables,
                null,
                null
            );

            if ($result['success']) {
                $smsMessageResident->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_code' => $result['response_code'] ?? null,
                    'api_response' => $result['api_response'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                ]);

                // به‌روزرسانی has_been_sent در resident_report
                if ($report) {
                    $report->update(['has_been_sent' => true]);
                }

                // به‌روزرسانی total_sent در auto_sms
                $autoSms->increment('total_sent');
                $autoSms->update(['last_sent_at' => now()]);
            } else {
                $smsMessageResident->update([
                    'status' => 'failed',
                    'error_message' => $result['message'] ?? 'خطا در ارسال',
                    'response_code' => $result['response_code'] ?? null,
                    'api_response' => $result['api_response'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending auto SMS', [
                'auto_sms_id' => $autoSms->id,
                'resident_id' => $resident->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * استخراج متغیرها از الگو
     */
    protected function extractPatternVariables($patternText, Resident $resident, $report = null, $recordData = [])
    {
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[1])) {
            return [];
        }

        // دریافت اطلاعات resident
        $residentData = $this->getResidentData($resident);

        // دریافت اطلاعات report
        $reportData = null;
        if ($report) {
            $reportModel = $report->report ?? null;
            if ($reportModel) {
                $reportData = [
                    'title' => $reportModel->title,
                    'description' => $reportModel->description,
                    'category_name' => $reportModel->category->name ?? '',
                    'negative_score' => $reportModel->negative_score,
                    'type' => $reportModel->type ?? 'violation',
                ];
            }
        }

        // دریافت داده‌های تغییر یافته
        $changedData = $recordData['record'] ?? null;

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
                $value = $this->getVariableValue($variable, $residentData, $reportData, $changedData, $recordData['table'] ?? null);
                
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
    protected function getResidentData(Resident $resident)
    {
        try {
            $residentService = new ResidentService();
            $data = $residentService->getResidentById($resident->resident_id ?? $resident->id);
            
            if ($data) {
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Error getting resident data', [
                'resident_id' => $resident->id,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'resident' => [
                'id' => $resident->id,
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
    protected function getVariableValue($variable, $residentData, $reportData = null, $changedData = null, $tableName = null)
    {
        $field = $variable->table_field ?? '';
        $type = $variable->variable_type ?? 'user';
        $variableTableName = $variable->table_name ?? null;

        // اگر متغیر مربوط به جدول تغییر یافته است، از داده‌های تغییر یافته استفاده می‌کنیم
        if ($changedData && $variableTableName === $tableName) {
            $value = $changedData->{$field} ?? null;
            if ($value !== null) {
                return is_string($value) ? $value : (string)$value;
            }
        }
        
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
                return is_string($value) ? $value : (string)$value;
            }
        } elseif ($type === 'report' && $reportData) {
            $value = $reportData[$field] ?? '';
            return is_string($value) ? $value : (string)$value;
        }
        
        return '';
    }
}
