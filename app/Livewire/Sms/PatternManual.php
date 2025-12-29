<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Resident;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;
use App\Services\ResidentService;
use App\Jobs\SyncResidentsFromApi;

class PatternManual extends Component
{
    public $units = [];
    public $loading = true;
    public $error = null;
    public $search = '';
    public $expandedUnits = [];
    
    // Modal properties
    public $showModal = false;
    public $selectedResident = null;
    public $selectedReport = null;
    public $selectedPattern = null;
    public $reports = [];
    public $patterns = [];
    public $reportPatterns; // الگوهای مرتبط با گزارش انتخاب شده
    public $notes = '';
    public $syncing = false;
    public $syncMessage = '';
    public $result = null; // نتیجه ارسال SMS (مشابه PatternTest)
    public $showResult = false; // نمایش نتیجه (مشابه PatternTest)
    public $previewMessage = ''; // پیش‌نمایش پیام با متغیرهای جایگزین شده
    public $previewVariables = []; // متغیرهای استخراج شده برای پیش‌نمایش
    public $senderNumber = ''; // شماره فرستنده

    public function mount()
    {
        $this->reportPatterns = collect([]);
        // دریافت شماره فرستنده از config
        $this->senderNumber = config('services.melipayamak.pattern_from') 
                            ?? config('services.melipayamak.from') 
                            ?? 'تنظیم نشده';
        $this->loadUnits();
        $this->loadReports();
        $this->loadPatterns();
    }
    
    public function loadPatterns()
    {
        $this->patterns = Pattern::where('is_active', true)
            ->whereNotNull('pattern_code')
            ->orderBy('title')
            ->get();
    }
    
    public function loadReportPatterns()
    {
        if ($this->selectedReport) {
            $report = Report::find($this->selectedReport);
            if ($report) {
                $this->reportPatterns = $report->activePatterns()->get();
            } else {
                $this->reportPatterns = collect([]);
            }
        } else {
            $this->reportPatterns = collect([]);
        }
    }
    
    public function updatedSelectedReport($value)
    {
        $this->loadReportPatterns();
        // اگر الگوهای مرتبط وجود داشت، اولین الگو را به صورت پیش‌فرض انتخاب کن
        if ($this->reportPatterns && $this->reportPatterns->count() > 0 && !$this->selectedPattern) {
            $this->selectedPattern = $this->reportPatterns->first()->id;
        }
        $this->updatePreview();
    }
    
    public function updatedSelectedPattern($value)
    {
        $this->updatePreview();
    }
    
    /**
     * به‌روزرسانی پیش‌نمایش پیام با متغیرهای جایگزین شده
     */
    public function updatePreview()
    {
        $this->previewMessage = '';
        $this->previewVariables = [];
        
        if (!$this->selectedPattern || !$this->selectedResident) {
            return;
        }
        
        $pattern = Pattern::find($this->selectedPattern);
        if (!$pattern || !$pattern->pattern_code) {
            return;
        }
        
        try {
            // استخراج متغیرها
            $variables = $this->extractPatternVariables($pattern->text, $this->selectedResident);
            $this->previewVariables = $variables;
            
            // ساخت پیش‌نمایش پیام با جایگزینی متغیرها
            $previewText = $pattern->text;
            
            // جایگزینی متغیرها در متن - باید به ترتیب {0}, {1}, {2} جایگزین شوند
            preg_match_all('/\{(\d+)\}/', $pattern->text, $matches);
            if (!empty($matches[0])) {
                $usedIndices = array_unique(array_map('intval', $matches[1]));
                sort($usedIndices);
                
                foreach ($usedIndices as $varIndex) {
                    $match = '{' . $varIndex . '}';
                    $arrayIndex = array_search($varIndex, $usedIndices);
                    
                    if (isset($variables[$arrayIndex]) && !empty($variables[$arrayIndex])) {
                        $value = htmlspecialchars($variables[$arrayIndex]);
                        $previewText = str_replace($match, '<strong style="color: #4361ee; background: #e0e7ff; padding: 2px 6px; border-radius: 3px;">{' . $varIndex . '}: ' . $value . '</strong>', $previewText);
                    } else {
                        $previewText = str_replace($match, '<span style="color: #dc3545; background: #ffe0e0; padding: 2px 6px; border-radius: 3px;">{' . $varIndex . '}: [مقدار یافت نشد]</span>', $previewText);
                    }
                }
            }
            
            $this->previewMessage = $previewText;
        } catch (\Exception $e) {
            \Log::error('Error updating preview', [
                'error' => $e->getMessage(),
                'pattern_id' => $this->selectedPattern,
            ]);
            $this->previewMessage = '<span style="color: #dc3545;">خطا در ساخت پیش‌نمایش: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
    }

    public function loadUnits()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $residentService = new ResidentService();
            $this->units = $residentService->getAllResidents();
            $this->sortData();
        } catch (\Exception $e) {
            $this->error = 'خطا در دریافت اطلاعات از دیتابیس: ' . $e->getMessage();
            $this->units = [];
        }

        $this->loading = false;
    }

    /**
     * همگام‌سازی دستی داده‌های اقامت‌گران از API
     */
    public function syncResidents()
    {
        $this->syncing = true;
        $this->syncMessage = 'در حال همگام‌سازی...';
        
        try {
            // اجرای Job همگام‌سازی
            $job = new SyncResidentsFromApi();
            $job->handle();
            
            // دریافت آمار همگام‌سازی
            $lastSync = \Illuminate\Support\Facades\Cache::get('residents_last_sync');
            
            // بارگذاری مجدد داده‌ها
            $this->loadUnits();
            
            if ($lastSync) {
                $message = "همگام‌سازی با موفقیت انجام شد. ";
                $message .= "تعداد: {$lastSync['synced_count']}, ";
                $message .= "ایجاد شده: {$lastSync['created_count']}, ";
                $message .= "به‌روزرسانی شده: {$lastSync['updated_count']}";
            } else {
                $message = 'همگام‌سازی با موفقیت انجام شد.';
            }
            
            // نمایش آلارم در بالا سمت چپ
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'بروزرسانی شد',
                'message' => $message,
                'duration' => 5000, // 5 ثانیه
            ]);
            
            // پاک کردن پیام همگام‌سازی از صفحه
            $this->syncMessage = '';
        } catch (\Exception $e) {
            \Log::error('Error syncing residents from PatternManual component', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // نمایش آلارم خطا در بالا سمت چپ
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا!',
                'message' => 'خطا در همگام‌سازی داده‌ها: ' . $e->getMessage(),
                'duration' => 5000,
            ]);
            
            // پاک کردن پیام همگام‌سازی از صفحه
            $this->syncMessage = '';
        } finally {
            $this->syncing = false;
        }
    }

    private function sortData()
    {
        usort($this->units, function ($a, $b) {
            return $a['unit']['code'] <=> $b['unit']['code'];
        });

        foreach ($this->units as &$unit) {
            usort($unit['rooms'], function ($a, $b) {
                $aNum = intval(preg_replace('/[^0-9]/', '', $a['name']));
                $bNum = intval(preg_replace('/[^0-9]/', '', $b['name']));
                return $aNum <=> $bNum;
            });
        }
    }

    public function loadReports()
    {
        $this->reports = Report::with('category')->get();
    }

    public function openModal($resident, $bed, $unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];

        // پیدا کردن resident در جدول residents بر اساس resident_id از API
        $residentApiId = $resident['id']; // این resident_id از API است
        $residentDb = Resident::where('resident_id', $residentApiId)->first();
        $residentDbId = $residentDb ? $residentDb->id : null;
        
        $this->selectedResident = [
            'id' => $resident['id'], // resident_id از API
            'db_id' => $residentDbId, // id از جدول residents
            'name' => $resident['full_name'],
            'phone' => $resident['phone'],
            'bed_id' => $bed['id'],
            'bed_name' => $bed['name'],
            'unit_id' => $unit['unit']['id'],
            'unit_name' => $unit['unit']['name'],
            'room_id' => $room['id'],
            'room_name' => $room['name']
        ];

        $this->selectedReport = null;
        $this->selectedPattern = null;
        $this->reportPatterns = collect([]);
        $this->notes = '';
        $this->previewMessage = '';
        $this->previewVariables = [];
        $this->result = null; // پاک کردن نتیجه قبلی
        $this->showResult = false;
        $this->showModal = true;
    }

    public function submit()
    {
        if (!$this->selectedReport) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً یک گزارش را انتخاب کنید.'
            ]);
            return;
        }

        // بررسی انتخاب الگو
        if (!$this->selectedPattern) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً یک الگو را انتخاب کنید.'
            ]);
            return;
        }

        if (empty($this->selectedResident['phone'])) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'شماره تلفن اقامتگر موجود نیست.'
            ]);
            return;
        }

        try {
            // پیدا کردن resident در جدول residents بر اساس resident_id از API
            $residentApiId = $this->selectedResident['id']; // این resident_id از API است
            $residentDbId = $this->selectedResident['db_id'] ?? null;
            
            // اگر db_id وجود نداشت، از دیتابیس پیدا کن
            if (!$residentDbId) {
                $resident = Resident::where('resident_id', $residentApiId)->first();
                $residentDbId = $resident ? $resident->id : null;
            }
            
            // دریافت اطلاعات گزارش برای بررسی نوع آن
            $report = Report::with('category')->find($this->selectedReport);
            
            // ثبت گزارش در جدول resident_reports
            $residentReport = ResidentReport::create([
                'report_id' => $this->selectedReport,
                'resident_id' => $residentDbId, // استفاده از id جدول residents
                'resident_name' => $this->selectedResident['name'],
                'phone' => $this->selectedResident['phone'],
                'unit_id' => $this->selectedResident['unit_id'],
                'unit_name' => $this->selectedResident['unit_name'],
                'room_id' => $this->selectedResident['room_id'],
                'room_name' => $this->selectedResident['room_name'],
                'bed_id' => $this->selectedResident['bed_id'],
                'bed_name' => $this->selectedResident['bed_name'],
                'notes' => $this->notes,
            ]);
            
            \Log::info('Resident report created', [
                'resident_report_id' => $residentReport->id,
                'report_id' => $this->selectedReport,
                'report_type' => $report->type ?? 'violation',
                'report_title' => $report->title ?? '',
                'resident_id' => $residentDbId,
            ]);

            $melipayamakService = new MelipayamakService();
            $result = null;
            $smsMessageResident = null;

            // ارسال با الگو
            $pattern = Pattern::find($this->selectedPattern);
            
            if (!$pattern || !$pattern->pattern_code) {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'الگوی انتخاب شده معتبر نیست یا کد الگو ندارد.'
                ]);
                return;
            }

            // استخراج متغیرها از متن الگو
            \Log::info('Before extracting pattern variables', [
                'pattern_id' => $pattern->id,
                'pattern_text' => $pattern->text,
                'pattern_code' => $pattern->pattern_code,
                'selected_resident' => $this->selectedResident,
                'selected_report' => $this->selectedReport,
            ]);
            
            $variables = $this->extractPatternVariables($pattern->text, $this->selectedResident);
            
            \Log::info('Pattern variables extracted for SMS', [
                'pattern_id' => $pattern->id,
                'pattern_text' => $pattern->text,
                'variables' => $variables,
                'variables_count' => count($variables),
                'variables_string' => implode(';', $variables),
                'variables_is_array' => is_array($variables),
                'variables_empty' => empty($variables),
            ]);
            
            // بررسی اینکه آیا متغیرها خالی هستند
            if (empty($variables)) {
                \Log::warning('Pattern variables are empty!', [
                    'pattern_id' => $pattern->id,
                    'pattern_text' => $pattern->text,
                ]);
                
                // اگر متغیرها خالی هستند، به کاربر هشدار بده اما ارسال را ادامه بده
                // چون ممکن است الگو متغیری نداشته باشد
            }
            
            // بررسی اینکه آیا pattern_code عدد است
            if (!is_numeric($pattern->pattern_code)) {
                \Log::error('Pattern code is not numeric!', [
                    'pattern_id' => $pattern->id,
                    'pattern_code' => $pattern->pattern_code,
                    'pattern_code_type' => gettype($pattern->pattern_code),
                ]);
                
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'کد الگو معتبر نیست. کد الگو باید یک عدد باشد.'
                ]);
                return;
            }
            
            // بررسی شماره تلفن
            $phone = $this->selectedResident['phone'] ?? '';
            if (empty($phone)) {
                \Log::error('Phone number is empty!', [
                    'selected_resident' => $this->selectedResident,
                ]);
                
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'شماره تلفن اقامتگر موجود نیست.'
                ]);
                return;
            }
            
            // استفاده از residentDbId که قبلاً پیدا شده
            // ایجاد رکورد در جدول sms_message_residents
            $smsMessageResident = SmsMessageResident::create([
                'report_id' => $this->selectedReport,
                'pattern_id' => $pattern->id,
                'is_pattern' => true,
                'pattern_variables' => implode(';', $variables), // متغیرها با ; جدا می‌شوند
                'resident_id' => $residentDbId, // استفاده از id جدول residents
                'resident_name' => $this->selectedResident['name'],
                'phone' => $phone,
                'title' => $pattern->title,
                'description' => $pattern->text,
                'status' => 'pending',
            ]);

            // ارسال پیامک با الگو
            \Log::info('Sending pattern-based SMS - Final Check', [
                'phone' => $phone,
                'pattern_code' => $pattern->pattern_code,
                'pattern_id' => $pattern->id,
                'variables' => $variables,
                'variables_count' => count($variables),
                'variables_string' => implode(';', $variables),
                'variables_type' => gettype($variables),
                'variables_is_array' => is_array($variables),
                'bodyId_type' => gettype($pattern->pattern_code),
                'bodyId_numeric' => is_numeric($pattern->pattern_code),
            ]);
            
            // اطمینان از اینکه variables یک آرایه است
            if (!is_array($variables)) {
                \Log::error('Variables is not an array!', [
                    'variables_type' => gettype($variables),
                    'variables_value' => $variables,
                ]);
                $variables = [];
            }
            
            // اطمینان از اینکه pattern_code عدد است
            $bodyId = (int)$pattern->pattern_code;
            
            \Log::info('Calling sendByBaseNumber2', [
                'to' => $phone,
                'bodyId' => $bodyId,
                'variables' => $variables,
                'variables_count' => count($variables),
            ]);
            
            $result = $melipayamakService->sendByBaseNumber2(
                $phone,
                $bodyId,
                $variables // آرایه متغیرها: ['علی احمدی', '1404/10/07']
            );
            
            \Log::info('Pattern-based SMS result', [
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'No message',
                'response_code' => $result['response_code'] ?? null,
                'rec_id' => $result['rec_id'] ?? null,
                'raw_response' => $result['raw_response'] ?? null,
                'http_status_code' => $result['http_status_code'] ?? null,
                'full_result' => $result,
            ]);

            // ارسال پاسخ به console.log
            $this->dispatch('logMelipayamakResponse', $result);

            // لاگ کامل نتیجه برای دیباگ
            \Log::info('Full SMS result before processing', [
                'result' => $result,
                'result_type' => gettype($result),
                'is_array' => is_array($result),
            ]);

            // بررسی اینکه آیا result تعریف شده است
            if (!$result || !is_array($result)) {
                \Log::error('SMS result is null', [
                    'selected_pattern' => $this->selectedPattern,
                ]);
                
                $errorHtml = '<div style="text-align: right; direction: rtl;">';
                $errorHtml .= '<p><strong>خطا در ارسال پیامک</strong></p>';
                $errorHtml .= '<div style="margin-top: 15px; padding: 12px; background: #fff3cd; border-radius: 5px; border-right: 3px solid #dc3545;">';
                $errorHtml .= '<strong style="color: #dc3545; display: block; margin-bottom: 8px;">✗ خطا:</strong>';
                $errorHtml .= '<div style="font-size: 13px; line-height: 1.8;">';
                $errorHtml .= '<span style="color: #dc3545;">نتیجه ارسال تعریف نشده است. لطفاً لاگ‌های سیستم را بررسی کنید.</span>';
                $errorHtml .= '</div>';
                $errorHtml .= '</div>';
                $errorHtml .= '</div>';
                
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'خطا در ارسال پیامک: نتیجه ارسال تعریف نشده است. لطفاً لاگ‌های سیستم را بررسی کنید.',
                    'html' => $errorHtml,
                ]);
                return;
            }

            // ذخیره نتیجه برای نمایش (مشابه PatternTest)
            $this->result = $result;
            $this->showResult = true;

            // بررسی موفقیت ارسال
            $isSuccess = isset($result['success']) && $result['success'] === true;
            
            \Log::info('PatternManual - SMS result saved', [
                'result' => $this->result,
                'showResult' => $this->showResult,
                'is_success' => $isSuccess,
                'result_keys' => is_array($this->result) ? array_keys($this->result) : 'not array',
            ]);
            
            // اطمینان از اینکه Livewire re-render می‌شود
            $this->dispatch('$refresh');
            
            if ($isSuccess) {
                $smsMessageResident->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_code' => $result['response_code'] ?? null,
                    'error_message' => null,
                ]);
                
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'گزارش ثبت شد و پیامک با موفقیت ارسال شد.',
                ]);
            } else {
                $smsMessageResident->update([
                    'status' => 'failed',
                    'error_message' => $result['message'],
                    'response_code' => $result['response_code'] ?? null,
                    'api_response' => $result['api_response'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                ]);
                
                $this->dispatch('showAlert', [
                    'type' => 'warning',
                    'title' => 'توجه!',
                    'text' => 'گزارش ثبت شد اما ارسال پیامک با خطا مواجه شد.',
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error in PatternManual SMS submit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'selected_resident' => $this->selectedResident ?? null,
                'selected_report' => $this->selectedReport ?? null,
            ]);
            
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ثبت گزارش و ارسال پیامک: ' . $e->getMessage(),
                'html' => '<div style="text-align: right; direction: rtl;"><p><strong>خطا در ثبت گزارش و ارسال پیامک</strong></p><p style="color: #f72585;">' . htmlspecialchars($e->getMessage()) . '</p><p style="font-size: 11px; color: #666; margin-top: 10px;">لطفاً لاگ‌های سیستم را بررسی کنید.</p></div>'
            ]);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedResident = null;
        $this->selectedReport = null;
        $this->selectedPattern = null;
        $this->reportPatterns = collect([]);
        $this->notes = '';
        $this->previewMessage = '';
        $this->previewVariables = [];
        $this->result = null; // پاک کردن نتیجه
        $this->showResult = false;
    }


    public function toggleUnitExpansion($unitIndex)
    {
        if (in_array($unitIndex, $this->expandedUnits)) {
            $this->expandedUnits = array_diff($this->expandedUnits, [$unitIndex]);
        } else {
            $this->expandedUnits[] = $unitIndex;
        }
    }

    public function getFilteredUnits()
    {
        $filteredUnits = $this->units;

        if (!empty($this->search)) {
            $searchTerm = strtolower($this->search);
            $filteredUnits = array_filter($filteredUnits, function ($unit) use ($searchTerm) {
                foreach ($unit['rooms'] as $room) {
                    if (strpos(strtolower($room['name']), $searchTerm) !== false) {
                        return true;
                    }
                    foreach ($room['beds'] as $bed) {
                        if ($bed['resident'] && (
                            strpos(strtolower($bed['resident']['full_name']), $searchTerm) !== false ||
                            strpos(strtolower($bed['resident']['phone']), $searchTerm) !== false
                        )) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }

        return array_values($filteredUnits);
    }

    /**
     * استخراج و جایگزینی متغیرها در الگو
     * متغیرها به ترتیب {0}, {1}, {2} و ... استخراج می‌شوند
     * و مقادیر آنها از دیتابیس (pattern_variables) و داده‌های کاربر/گزارش استخراج می‌شود
     */
    protected function extractPatternVariables($patternText, $resident)
    {
        // پیدا کردن تمام متغیرها در الگو (مثل {0}, {1}, {2})
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[1])) {
            return []; // اگر متغیری وجود نداشت
        }

        // دریافت اطلاعات کامل resident از دیتابیس
        $residentData = $this->getResidentData($resident);
        
        // دریافت اطلاعات گزارش
        $reportData = null;
        if ($this->selectedReport) {
            $report = Report::with('category')->find($this->selectedReport);
            if ($report) {
                $reportData = [
                    'title' => $report->title,
                    'description' => $report->description,
                    'category_name' => $report->category->name ?? '',
                    'negative_score' => $report->negative_score,
                    'type' => $report->type ?? 'violation',
                ];
            }
        }

        // بارگذاری متغیرها از دیتابیس
        $variables = PatternVariable::where('is_active', true)
            ->get()
            ->keyBy('code'); // کلید بر اساس کد (مثل {0}, {1})

        $result = [];
        $usedIndices = array_unique(array_map('intval', $matches[1]));
        sort($usedIndices); // مرتب‌سازی بر اساس ترتیب در الگو

        \Log::debug('Extracting pattern variables', [
            'pattern_text' => $patternText,
            'used_indices' => $usedIndices,
            'resident_id' => $resident['id'] ?? null,
            'report_id' => $this->selectedReport ?? null,
        ]);

        \Log::info('Pattern variables from database', [
            'total_variables' => $variables->count(),
            'variable_codes' => $variables->keys()->toArray(),
            'used_indices' => $usedIndices,
        ]);

        foreach ($usedIndices as $index) {
            $code = '{' . $index . '}';
            $variable = $variables->get($code);

            if ($variable) {
                $value = $this->getVariableValue($variable, $residentData, $reportData);
                
                // اطمینان از اینکه value یک رشته است
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                
                // اگر مقدار خالی است، حداقل یک فاصله بگذار تا API خطا ندهد
                if (empty(trim($value))) {
                    \Log::warning('Variable value is empty', [
                        'code' => $code,
                        'index' => $index,
                        'table_field' => $variable->table_field,
                        'variable_type' => $variable->variable_type,
                    ]);
                    $value = ''; // مقدار خالی - API باید آن را قبول کند
                }
                
                \Log::info('Variable extracted successfully', [
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
                \Log::error('Variable not found in database', [
                    'code' => $code,
                    'index' => $index,
                    'pattern_text' => $patternText,
                    'available_variables' => $variables->keys()->toArray(),
                ]);
                $result[] = ''; // مقدار خالی برای متغیرهای پیدا نشده
            }
        }

        \Log::debug('Pattern variables extracted', [
            'variables' => $result,
            'variables_count' => count($result),
        ]);

        return $result;
    }

    /**
     * دریافت اطلاعات کامل resident از API
     */
    protected function getResidentData($resident)
    {
        try {
            $residentService = new ResidentService();
            $data = $residentService->getResidentById($resident['id']); // resident_id از API
            
            \Log::debug('Resident data from API', [
                'resident_id' => $resident['id'] ?? null,
                'data_received' => $data ? 'yes' : 'no',
                'data_keys' => $data ? array_keys($data) : [],
            ]);
            
            if ($data) {
                return $data;
            }
        } catch (\Exception $e) {
            \Log::error('Error getting resident data', [
                'resident_id' => $resident['id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // در صورت خطا، از داده‌های موجود استفاده می‌کنیم
        $fallbackData = [
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
        
        \Log::debug('Using fallback resident data', [
            'fallback_data' => $fallbackData,
        ]);
        
        return $fallbackData;
    }

    /**
     * دریافت مقدار متغیر بر اساس فیلد جدول
     * این متد مقدار متغیر را از داده‌های resident یا report استخراج می‌کند
     */
    protected function getVariableValue($variable, $residentData, $reportData)
    {
        $field = $variable->table_field ?? '';
        $type = $variable->variable_type ?? 'user';
        
        \Log::debug('Getting variable value', [
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
                \Log::debug('Getting unit field', [
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
                \Log::debug('Getting room field', [
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
                \Log::debug('Getting bed field', [
                    'field' => $field,
                    'key' => $key,
                    'value' => $value,
                ]);
                return $value;
            } else {
                // فیلدهای مستقیم resident (مثل full_name, phone, name, national_id, etc.)
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
                    } elseif ($field === 'national_id' || $field === 'national_code') {
                        $value = $residentData['resident']['national_id'] ?? 
                                 $residentData['resident']['national_code'] ?? '';
                    }
                }
                
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                \Log::debug('Getting resident field', [
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
                \Log::debug('Getting category field', [
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
                \Log::debug('Getting report field', [
                    'field' => $field,
                    'value' => $value,
                    'available_fields' => array_keys($reportData),
                ]);
                return $value;
            }
        } elseif ($type === 'general') {
            // فیلدهای عمومی
            if ($field === 'today') {
                $value = $this->formatJalaliDate(now()->toDateString());
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                \Log::debug('Getting general field', [
                    'field' => $field,
                    'value' => $value,
                ]);
                return $value;
            }
        }

        \Log::warning('Variable value not found', [
            'field' => $field,
            'type' => $type,
            'variable_id' => $variable->id ?? null,
        ]);

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

    public function render()
    {
        $filteredUnits = $this->getFilteredUnits();

        return view('livewire.sms.pattern-manual', [
            'filteredUnits' => $filteredUnits
        ]);
    }
}
