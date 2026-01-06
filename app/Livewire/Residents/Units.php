<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\Report;
use App\Models\Category;
use App\Models\Resident;
use App\Models\ResidentReport;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
use App\Models\SenderNumber;
use App\Services\ResidentService;
use App\Services\MelipayamakService;
use Illuminate\Support\Facades\Log;

class Units extends Component
{
    public $units = [];
    public $loading = true;
    public $error = null;
    public $search = '';
    public $filterEmptyBeds = false;
    public $selectedResidents = [];
    public $showReportModal = false;
    public $reportType = 'individual';
    public $currentResident = null;
    public $currentRoom = null;
    public $categories = [];
    public $reports = [];
    public $selectedReports = []; // آرایه برای سازگاری، اما فقط یک گزارش انتخاب می‌شود
    public $notes = '';
    public $expandedUnits = [];
    public $reportModalLoading = false;
    public $lastSubmittedReports = []; // آخرین گزارش‌های ثبت شده
    public $showSubmissionResult = false; // نمایش نتیجه ثبت
    public $databaseResponse = null; // پاسخ دیتابیس برای نمایش در مودال
    public $reportCheckError = null; // پیام خطا برای چک نشدن همه گزارش‌ها
    public $showSmsResponseModal = false; // نمایش modal پاسخ SMS
    public $smsResponses = []; // پاسخ‌های SMS برای نمایش در modal

    public function mount()
    {
        $this->loadUnits();
        $this->loadReportData();
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
        $this->loadUnits();
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
            $this->units = $this->getSampleData();
        }

        $this->loading = false;
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
            
            // اضافه کردن bed_count به هر room برای استفاده در view
            foreach ($unit['rooms'] as &$room) {
                $room['bed_count'] = count(array_filter($room['beds'] ?? [], function($bed) {
                    return !empty($bed['resident']);
                }));
            }
        }
    }

    public function loadReportData()
    {
        $this->categories = Category::with('reports')->get()->toArray();
        $this->reports = Report::all()->toArray();
    }

    public function openIndividualReport($resident, $bed, $unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];

        $this->reportType = 'individual';
        $this->currentResident = [
            'id' => $resident['id'],
            'name' => $resident['full_name'] ?? null,
            'phone' => $resident['phone'] ?? null,
            'job' => $resident['job'] ?? null,
            'bed_id' => $bed['id'],
            'bed_name' => $bed['name'],
            'unit_id' => $unit['unit']['id'],
            'unit_name' => $unit['unit']['name'],
            'room_id' => $room['id'],
            'room_name' => $room['name']
        ];

        $this->loadReportData();
        $this->selectedReports = [];
        $this->notes = '';
        $this->showReportModal = true;
        $this->dispatch('modal-opened');
    }

    public function openGroupReportFromRoom($unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];

        $roomResidents = [];
        foreach ($room['beds'] as $bed) {
            if ($bed['resident']) {
                $key = $unitIndex . '_' . $roomIndex . '_' . $bed['id'];
                $roomResidents[$key] = [
                    'resident_id' => $bed['resident']['id'],
                    'resident_name' => $bed['resident']['full_name'] ?? null,
                    'phone' => $bed['resident']['phone'] ?? null,
                    'job' => $bed['resident']['job'] ?? null,
                    'bed_id' => $bed['id'],
                    'bed_name' => $bed['name'],
                    'unit_id' => $unit['unit']['id'],
                    'unit_name' => $unit['unit']['name'],
                    'room_id' => $room['id'],
                    'room_name' => $room['name']
                ];
            }
        }

        if (empty($roomResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'این اتاق هیچ اقامت‌گری ندارد.'
            ]);
            return;
        }

        $this->reportType = 'group';
        $this->currentRoom = [
            'unit_id' => $unit['unit']['id'],
            'unit_name' => $unit['unit']['name'],
            'room_id' => $room['id'],
            'room_name' => $room['name']
        ];
        $this->selectedResidents = $roomResidents;

        $this->loadReportData();
        $this->selectedReports = [];
        $this->notes = '';
        $this->showReportModal = true;
        $this->dispatch('modal-opened');
    }

    public function toggleUnitExpansion($unitIndex)
    {
        if (in_array($unitIndex, $this->expandedUnits)) {
            $this->expandedUnits = array_diff($this->expandedUnits, [$unitIndex]);
        } else {
            $this->expandedUnits[] = $unitIndex;
        }
    }

    public function getViolationReportsCount($residentId)
    {
        if (!$residentId) {
            return 0;
        }
        
        // پیدا کردن resident از جدول residents بر اساس resident_id
        $resident = Resident::where('resident_id', $residentId)->first();
        if (!$resident) {
            return 0;
        }
        
        // شمارش تعداد گزارش‌های تخلف (category_id = 1)
        // resident_id در ResidentReport به id در جدول residents اشاره می‌کند
        $count = ResidentReport::where('resident_id', $resident->id)
            ->whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->count();
        
        return $count;
    }

    public function getJobTitle($job)
    {
        $jobs = [
            'daneshjo_dolati' => 'دانشجوی دولتی',
            'daneshjo_azad' => 'دانشجوی آزاد',
            'daneshjo_other' => 'سایر دانشجویان',
            'karmand_shakhse' => 'کارمند بخش خصوصی',
            'karmand_dolat' => 'کارمند دولت',
            'nurse' => 'پرستار',
            'azad' => 'آزاد',
            'other' => 'سایر'
        ];
        return $jobs[$job] ?? $job;
    }

    public function openSelectedGroupReport()
    {
        if (empty($this->selectedResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک اقامت‌گر را انتخاب کنید.'
            ]);
            return;
        }

        $this->reportType = 'group';
        $this->loadReportData();
        $this->selectedReports = [];
        $this->notes = '';
        $this->showReportModal = true;
        $this->dispatch('modal-opened');
    }

    public function submitReport()
    {
        if (empty($this->selectedReports)) {
            $this->databaseResponse = [
                'success' => false,
                'message' => 'لطفاً حداقل یک گزارش را انتخاب کنید.'
            ];
            return;
        }

        // بررسی اینکه آیا همه گزارش‌های قبلی چک شده‌اند یا نه - غیرفعال شده است
        // $uncheckedReports = $this->checkAllReportsAreChecked();
        // if ($uncheckedReports['has_unchecked']) {
        //     $this->reportModalLoading = false;
        //     $this->reportCheckError = 'لطفا همه رو چک کنید';
        //     return;
        // }
        
        // اگر همه چک شده‌اند، پیام خطا را پاک کن
        $this->reportCheckError = null;
        
        // پاک کردن پاسخ‌های SMS قبلی
        $this->smsResponses = [];
        $this->showSmsResponseModal = false;

        $this->reportModalLoading = true;
        $errors = [];
        $successCount = 0;
        $failedCount = 0;

        try {
            if ($this->reportType === 'individual') {
                $result = $this->submitIndividualReport();
                $errors = $result['errors'] ?? [];
                $successCount = $result['success'] ?? 0;
                $failedCount = $result['failed'] ?? 0;
            } else {
                $result = $this->submitGroupReport();
                $errors = $result['errors'] ?? [];
                $successCount = $result['success'] ?? 0;
                $failedCount = $result['failed'] ?? 0;
            }

            // منتظر ماندن برای ارسال پیامک‌ها (Listener sync است اما برای اطمینان تاخیر می‌گذاریم)
            if ($successCount > 0) {
                // تاخیر برای اطمینان از ارسال پیامک‌ها
                // هر گزارش حدود 0.5 تا 1 ثانیه زمان می‌برد (برای ارسال پیامک)
                $delay = min($successCount * 800000, 3000000); // حداکثر 3 ثانیه
                usleep($delay);
            }

            if ($failedCount > 0) {
                $errorMessage = "{$successCount} گزارش با موفقیت ثبت شد. {$failedCount} گزارش با خطا مواجه شد.\n\n";
                $errorMessage .= "خطاها:\n";
                
                // پردازش خطاها - اگر آرایه است، آن را به رشته تبدیل می‌کنیم
                $errorStrings = [];
                foreach (array_slice($errors, 0, 5) as $error) {
                    if (is_array($error)) {
                        if (isset($error['error'])) {
                            $errorStrings[] = $error['error'];
                        } elseif (isset($error['report_id'])) {
                            $errorStrings[] = "گزارش ID {$error['report_id']}: " . ($error['error'] ?? 'خطای نامشخص');
                        } else {
                            $errorStrings[] = json_encode($error, JSON_UNESCAPED_UNICODE);
                        }
                    } else {
                        $errorStrings[] = (string)$error;
                    }
                }
                
                $errorMessage .= implode("\n", $errorStrings);
                if (count($errors) > 5) {
                    $errorMessage .= "\n... و " . (count($errors) - 5) . " خطای دیگر";
                }
                
                $this->databaseResponse = [
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $errors
                ];
                // بستن مودال حتی در صورت خطا (اگر حداقل یک گزارش موفق ثبت شد)
                if ($successCount > 0) {
                    $this->closeModal();
                }
            } else {
                // ذخیره پاسخ دیتابیس برای نمایش در مودال
                $this->databaseResponse = [
                    'success' => true,
                    'message' => "{$successCount} گزارش با موفقیت در دیتابیس ثبت شد.",
                    'reports' => $result['submitted_reports'] ?? []
                ];
                
                // باز کردن modal پاسخ SMS اگر پیامکی ارسال شده باشد
                \Log::info('Checking SMS Responses before opening modal', [
                    'sms_responses_count' => count($this->smsResponses),
                    'sms_responses' => $this->smsResponses,
                ]);
                
                if (!empty($this->smsResponses)) {
                    $this->showSmsResponseModal = true;
                    \Log::info('Opening SMS Response Modal', [
                        'sms_responses_count' => count($this->smsResponses),
                        'show_sms_response_modal' => $this->showSmsResponseModal,
                    ]);
                } else {
                    \Log::warning('SMS Responses is empty - Modal will not open', [
                        'sms_responses_count' => count($this->smsResponses),
                    ]);
                }
                
                // لاگ پاسخ دیتابیس در کنسول
                $this->dispatch('logDatabaseResponse', [
                    'success' => true,
                    'count' => $successCount,
                    'reports' => $result['submitted_reports'] ?? []
                ]);
                
                // بستن مودال بعد از ثبت موفق گزارش
                $this->closeModal();
                
                // نمایش نتایج در بالای صفحه
                $this->showSubmissionResult = true;
            }
        } catch (\Exception $e) {
            \Log::error('Error submitting report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'report_type' => $this->reportType,
                'selected_reports' => $this->selectedReports,
                'current_resident' => $this->currentResident,
            ]);

            $this->databaseResponse = [
                'success' => false,
                'message' => 'خطا در ثبت گزارش: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ];
            
            // لاگ خطا در کنسول
            $this->dispatch('logDatabaseResponse', [
                'success' => false,
                'error' => $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        } finally {
            $this->reportModalLoading = false;
        }
    }

    private function submitIndividualReport()
    {
        $errors = [];
        $successCount = 0;
        $failedCount = 0;
        $submittedReports = [];

        foreach ($this->selectedReports as $reportId) {
            try {
                // پیدا کردن ID واقعی resident در جدول residents
                $residentDbId = null;
                if (!empty($this->currentResident['id'])) {
                    // resident_id از API است، باید id واقعی را از جدول residents پیدا کنیم
                    $resident = \App\Models\Resident::where('resident_id', $this->currentResident['id'])->first();
                    $residentDbId = $resident ? $resident->id : null;
                }

                // ایجاد رکورد در دیتابیس
                $residentReport = \App\Models\ResidentReport::create([
                    'report_id' => $reportId,
                    'resident_id' => $residentDbId, // استفاده از id واقعی از جدول residents
                    'resident_name' => $this->currentResident['name'] ?? null,
                    'phone' => $this->currentResident['phone'] ?? null,
                    'unit_id' => $this->currentResident['unit_id'] ?? null,
                    'unit_name' => $this->currentResident['unit_name'] ?? null,
                    'room_id' => $this->currentResident['room_id'] ?? null,
                    'room_name' => $this->currentResident['room_name'] ?? null,
                    'bed_id' => $this->currentResident['bed_id'] ?? null,
                    'bed_name' => $this->currentResident['bed_name'] ?? null,
                    'notes' => $this->notes,
                ]);

                // ارسال مستقیم پیامک الگویی (مشابه GroupSms)
                $smsResult = null;
                $report = Report::with('category')->find($reportId);
                
                if ($report && !empty($this->currentResident['phone'])) {
                    // دریافت اولین الگوی فعال مرتبط با گزارش
                    $pattern = $report->activePatterns()
                        ->where('patterns.is_active', true)
                        ->whereNotNull('patterns.pattern_code')
                        ->first();
                    
                    if ($pattern && $pattern->pattern_code) {
                        try {
                            // دریافت اطلاعات resident از API
                            $residentService = new ResidentService();
                            $residentApiData = null;
                            if (!empty($this->currentResident['id'])) {
                                try {
                                    $residentApiData = $residentService->getResidentById($this->currentResident['id']);
                                } catch (\Exception $e) {
                                    Log::error('Error getting resident data from API', [
                                        'resident_id' => $this->currentResident['id'],
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }
                            
                            // پیدا کردن resident در جدول residents بر اساس resident_id از API
                            $residentDb = \App\Models\Resident::where('resident_id', $this->currentResident['id'])->first();
                            
                            // ساخت داده‌های resident از دیتابیس برای استخراج متغیرها
                            $residentDataForVariables = null;
                            if ($residentDb) {
                                $residentDataForVariables = [
                                    'id' => $residentDb->id,
                                    'resident_id' => $residentDb->resident_id,
                                    'resident_full_name' => $residentDb->resident_full_name,
                                    'resident_phone' => $residentDb->resident_phone,
                                    'unit_id' => $residentDb->unit_id,
                                    'unit_name' => $residentDb->unit_name,
                                    'unit_code' => $residentDb->unit_code,
                                    'room_id' => $residentDb->room_id,
                                    'room_name' => $residentDb->room_name,
                                    'room_code' => $residentDb->room_code,
                                    'bed_id' => $residentDb->bed_id,
                                    'bed_name' => $residentDb->bed_name,
                                    'bed_code' => $residentDb->bed_code,
                                    'contract_payment_date_jalali' => $residentDb->contract_payment_date_jalali,
                                    'contract_start_date_jalali' => $residentDb->contract_start_date_jalali,
                                    'contract_end_date_jalali' => $residentDb->contract_end_date_jalali,
                                    'resident_age' => $residentDb->resident_age,
                                    'resident_job' => $residentDb->resident_job,
                                ];
                            }
                            
                            // ساخت داده‌های resident برای استخراج متغیرها (سازگاری)
                            $residentData = [
                                'id' => $this->currentResident['id'] ?? null,
                                'db_id' => $residentDbId,
                                'resident_id' => $this->currentResident['id'] ?? null,
                                'resident_name' => $this->currentResident['name'] ?? '',
                                'name' => $this->currentResident['name'] ?? '',
                                'phone' => $this->currentResident['phone'] ?? '',
                                'unit_id' => $this->currentResident['unit_id'] ?? null,
                                'unit_name' => $this->currentResident['unit_name'] ?? '',
                                'room_id' => $this->currentResident['room_id'] ?? null,
                                'room_name' => $this->currentResident['room_name'] ?? '',
                                'bed_id' => $this->currentResident['bed_id'] ?? null,
                                'bed_name' => $this->currentResident['bed_name'] ?? '',
                            ];
                            
                            // استخراج متغیرها از متن الگو (با استفاده از داده‌های دیتابیس)
                            $variables = $this->extractPatternVariables($pattern->text, $residentData, $residentDataForVariables, $report);
                            
                            // دریافت شماره فرستنده
                            $senderNumber = SenderNumber::getActivePatternNumbers()->first();
                            $senderNumberValue = $senderNumber ? $senderNumber->number : null;
                            $apiKey = $senderNumber ? $senderNumber->api_key : null;
                            
                            // اگر API Key از sender number دریافت نشد، از جدول api_keys استفاده می‌کنیم
                            if (empty($apiKey)) {
                                $dbConsoleKey = \App\Models\ApiKey::getKeyValue('console_api_key');
                                $dbApiKey = \App\Models\ApiKey::getKeyValue('api key');
                                $configConsoleKey = config('services.melipayamak.console_api_key');
                                $configApiKey = config('services.melipayamak.api_key');
                                
                                $apiKey = $dbConsoleKey
                                    ?: $dbApiKey
                                    ?: $configConsoleKey
                                    ?: $configApiKey;
                            }
                            
                            // علامت‌گذاری که پیامک در حال ارسال است (برای جلوگیری از ارسال دوبار توسط Event Listener)
                            $residentReport->update(['has_been_sent' => true]);
                            
                            // ایجاد رکورد در sms_message_residents
                            $smsMessageResident = SmsMessageResident::create([
                                'sms_message_id' => null,
                                'report_id' => $reportId,
                                'pattern_id' => $pattern->id,
                                'is_pattern' => true,
                                'pattern_variables' => implode(';', $variables),
                                'resident_id' => $residentDbId,
                                'resident_name' => $this->currentResident['name'] ?? '',
                                'phone' => $this->currentResident['phone'] ?? '',
                                'title' => $pattern->title,
                                'description' => $pattern->text,
                                'status' => 'pending',
                            ]);
                            
                            // ارسال پیامک با الگو - استفاده از sendByBaseNumber2 (مشابه PatternManual)
                            $melipayamakService = new MelipayamakService();
                            $bodyId = (int)$pattern->pattern_code;
                            
                            $result = $melipayamakService->sendByBaseNumber2(
                                $this->currentResident['phone'],
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
                                    'rec_id' => $result['rec_id'] ?? null,
                                    'api_response' => $result['api_response'] ?? null,
                                    'raw_response' => $result['raw_response'] ?? null,
                                ]);
                            } else {
                                $smsMessageResident->update([
                                    'status' => 'failed',
                                    'error_message' => $result['message'] ?? 'خطا در ارسال',
                                    'response_code' => $result['response_code'] ?? null,
                                    'rec_id' => $result['rec_id'] ?? null,
                                    'api_response' => $result['api_response'] ?? null,
                                    'raw_response' => $result['raw_response'] ?? null,
                                ]);
                            }
                            
                            // refresh برای دریافت داده‌های جدید
                            $smsMessageResident->refresh();
                            
                            // ذخیره نتیجه برای نمایش
                            $smsResult = $smsMessageResident;
                            
                            Log::info('Units - SMS sent successfully', [
                                'sms_message_resident_id' => $smsMessageResident->id,
                                'status' => $smsMessageResident->status,
                                'rec_id' => $smsMessageResident->rec_id,
                                'response_code' => $smsMessageResident->response_code,
                            ]);
                            
                        } catch (\Exception $e) {
                            Log::error('Error sending SMS in Units', [
                                'report_id' => $reportId,
                                'resident_id' => $residentDbId,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }
                }

                // لاگ برای بررسی ذخیره‌سازی
                \Log::info('گزارش در دیتابیس ذخیره شد', [
                    'resident_report_id' => $residentReport->id,
                    'report_id' => $reportId,
                    'resident_id' => $residentReport->resident_id,
                    'resident_db_id' => $residentDbId,
                    'resident_name' => $residentReport->resident_name,
                    'created_at' => $residentReport->created_at,
                    'sms_result_found' => $smsResult ? 'yes' : 'no',
                    'sms_result_id' => $smsResult ? $smsResult->id : null,
                    'sms_result_status' => $smsResult ? $smsResult->status : null,
                    'sms_result_rec_id' => $smsResult ? $smsResult->rec_id : null,
                    'sms_result_data' => $smsResult ? [
                        'status' => $smsResult->status,
                        'rec_id' => $smsResult->rec_id,
                        'response_code' => $smsResult->response_code,
                    ] : null,
                ]);

                // بررسی اینکه آیا رکورد واقعاً در دیتابیس ذخیره شده است
                $existsInDb = \App\Models\ResidentReport::where('id', $residentReport->id)->exists();
                if (!$existsInDb) {
                    throw new \Exception('رکورد در دیتابیس ذخیره نشد!');
                }

                // خواندن رکورد از دیتابیس برای نمایش پاسخ
                $submittedReport = \App\Models\ResidentReport::with(['report', 'report.category'])
                    ->find($residentReport->id);
                
                if (!$submittedReport) {
                    throw new \Exception('رکورد از دیتابیس خوانده نشد!');
                }
                
                // ساخت آرایه sms_result برای نمایش
                $smsResultArray = null;
                if ($smsResult) {
                    $smsResultArray = [
                        'status' => $smsResult->status ?? 'pending',
                        'success' => ($smsResult->status ?? 'pending') === 'sent',
                        'message' => ($smsResult->status ?? 'pending') === 'sent' 
                            ? ($smsResult->rec_id ? 'پیامک با موفقیت ارسال شد (RecId: ' . $smsResult->rec_id . ')' : 'پیامک با موفقیت ارسال شد')
                            : ($smsResult->error_message ?? 'خطا در ارسال'),
                        'response_code' => $smsResult->response_code ?? null,
                        'rec_id' => $smsResult->rec_id ?? null,
                        'error_message' => $smsResult->error_message ?? null,
                        'api_response' => $smsResult->api_response ?? null,
                        'raw_response' => $smsResult->raw_response ?? null,
                        'sent_at' => $smsResult->sent_at ? $smsResult->sent_at->toDateTimeString() : null,
                        // پاسخ کامل API برای نمایش دقیق
                        'full_api_response' => $smsResult->api_response ? (is_string($smsResult->api_response) ? json_decode($smsResult->api_response, true) : $smsResult->api_response) : null,
                        'full_raw_response' => $smsResult->raw_response ?? null,
                    ];
                    
                    \Log::info('Units - SMS result array created', [
                        'sms_result_array' => $smsResultArray,
                        'sms_result_status' => $smsResult->status,
                        'sms_result_rec_id' => $smsResult->rec_id,
                    ]);
                } else {
                    \Log::warning('Units - SMS result is null', [
                        'report_id' => $reportId,
                        'resident_db_id' => $residentDbId,
                    ]);
                }
                
                $submittedReports[] = [
                    'id' => $submittedReport->id,
                    'report_id' => $submittedReport->report_id,
                    'report_title' => $submittedReport->report->title ?? 'نامشخص',
                    'category_name' => $submittedReport->report->category->name ?? 'بدون دسته',
                    'resident_name' => $submittedReport->resident_name,
                    'phone' => $submittedReport->phone,
                    'unit_name' => $submittedReport->unit_name,
                    'room_name' => $submittedReport->room_name,
                    'bed_name' => $submittedReport->bed_name,
                    'notes' => $submittedReport->notes,
                    'created_at' => $submittedReport->created_at ? $submittedReport->created_at->toDateTimeString() : null,
                    'all_data' => $this->prepareArrayForJson($submittedReport), // تمام داده‌های رکورد
                    'sms_result' => $smsResultArray,
                ];
                
                \Log::info('Units - Submitted report added', [
                    'report_id' => $reportId,
                    'has_sms_result' => !empty($smsResultArray),
                    'sms_result_status' => $smsResultArray['status'] ?? null,
                ]);
                
                // ذخیره پاسخ SMS برای نمایش در modal
                if (!empty($smsResultArray)) {
                    $this->smsResponses[] = [
                        'report_id' => $reportId,
                        'report_title' => $submittedReport->report->title ?? 'نامشخص',
                        'resident_name' => $submittedReport->resident_name,
                        'phone' => $submittedReport->phone,
                        'sms_result' => $smsResultArray,
                    ];
                    
                    \Log::info('SMS Response added to array', [
                        'report_id' => $reportId,
                        'sms_responses_count' => count($this->smsResponses),
                        'sms_result_status' => $smsResultArray['status'] ?? null,
                    ]);
                    
                    // نمایش toast notification برای پاسخ ملی پیامک
                    $smsToastType = $smsResultArray['success'] ? 'success' : 'error';
                    $smsToastTitle = $smsResultArray['success'] ? 'پیامک ارسال شد' : 'خطا در ارسال پیامک';
                    $smsToastMessage = $smsResultArray['message'] ?? '';
                    if (!empty($smsResultArray['rec_id'])) {
                        $smsToastMessage .= ' (RecId: ' . $smsResultArray['rec_id'] . ')';
                    }
                    
                    $this->dispatch('showToast', [
                        'type' => $smsToastType,
                        'title' => $smsToastTitle,
                        'message' => $smsToastMessage,
                        'duration' => 0, // بسته نشود تا زمانی که کاربر روی ضربدر کلیک کند
                    ]);
                }

                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $errorMsg = "گزارش ID {$reportId}: " . $e->getMessage();
                $errors[] = [
                    'report_id' => $reportId,
                    'error' => $e->getMessage(),
                    'error_details' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'code' => $e->getCode(),
                    ]
                ];
            }
        }

        // ذخیره نتایج برای نمایش
        $this->lastSubmittedReports = $submittedReports;
        $this->showSubmissionResult = true;

        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors,
            'submitted_reports' => $submittedReports
        ];
    }

    private function submitGroupReport()
    {
        $errors = [];
        $successCount = 0;
        $failedCount = 0;
        $submittedReports = [];

        foreach ($this->selectedResidents as $residentData) {
            foreach ($this->selectedReports as $reportId) {
                try {
                    // پیدا کردن ID واقعی resident در جدول residents
                    $residentDbId = null;
                    if (!empty($residentData['resident_id'])) {
                        // resident_id از API است، باید id واقعی را از جدول residents پیدا کنیم
                        $resident = \App\Models\Resident::where('resident_id', $residentData['resident_id'])->first();
                        $residentDbId = $resident ? $resident->id : null;
                    }

                    // ایجاد رکورد در دیتابیس
                    $residentReport = \App\Models\ResidentReport::create([
                        'report_id' => $reportId,
                        'resident_id' => $residentDbId, // استفاده از id واقعی از جدول residents
                        'resident_name' => $residentData['resident_name'] ?? null,
                        'phone' => $residentData['phone'] ?? null,
                        'unit_id' => $residentData['unit_id'] ?? null,
                        'unit_name' => $residentData['unit_name'] ?? null,
                        'room_id' => $residentData['room_id'] ?? null,
                        'room_name' => $residentData['room_name'] ?? null,
                        'bed_id' => $residentData['bed_id'] ?? null,
                        'bed_name' => $residentData['bed_name'] ?? null,
                        'notes' => $this->notes,
                    ]);

                    // ارسال مستقیم پیامک الگویی (مشابه GroupSms)
                    $smsResult = null;
                    $report = Report::with('category')->find($reportId);
                    
                    if ($report && !empty($residentData['phone'])) {
                        // دریافت اولین الگوی فعال مرتبط با گزارش
                        $pattern = $report->activePatterns()
                            ->where('patterns.is_active', true)
                            ->whereNotNull('patterns.pattern_code')
                            ->first();
                        
                        if ($pattern && $pattern->pattern_code) {
                            try {
                                // دریافت اطلاعات resident از API
                                $residentService = new ResidentService();
                                $residentApiData = null;
                                if (!empty($residentData['resident_id'])) {
                                    try {
                                        $residentApiData = $residentService->getResidentById($residentData['resident_id']);
                                    } catch (\Exception $e) {
                                        Log::error('Error getting resident data from API', [
                                            'resident_id' => $residentData['resident_id'],
                                            'error' => $e->getMessage(),
                                        ]);
                                    }
                                }
                                
                                // پیدا کردن resident در جدول residents بر اساس resident_id از API
                                $residentDb = \App\Models\Resident::where('resident_id', $residentData['resident_id'])->first();
                                
                                // ساخت داده‌های resident از دیتابیس برای استخراج متغیرها
                                $residentDataForVariables = null;
                                if ($residentDb) {
                                    $residentDataForVariables = [
                                        'id' => $residentDb->id,
                                        'resident_id' => $residentDb->resident_id,
                                        'resident_full_name' => $residentDb->resident_full_name,
                                        'resident_phone' => $residentDb->resident_phone,
                                        'unit_id' => $residentDb->unit_id,
                                        'unit_name' => $residentDb->unit_name,
                                        'unit_code' => $residentDb->unit_code,
                                        'room_id' => $residentDb->room_id,
                                        'room_name' => $residentDb->room_name,
                                        'room_code' => $residentDb->room_code,
                                        'bed_id' => $residentDb->bed_id,
                                        'bed_name' => $residentDb->bed_name,
                                        'bed_code' => $residentDb->bed_code,
                                        'contract_payment_date_jalali' => $residentDb->contract_payment_date_jalali,
                                        'contract_start_date_jalali' => $residentDb->contract_start_date_jalali,
                                        'contract_end_date_jalali' => $residentDb->contract_end_date_jalali,
                                        'resident_age' => $residentDb->resident_age,
                                        'resident_job' => $residentDb->resident_job,
                                    ];
                                }
                                
                                // ساخت داده‌های resident برای استخراج متغیرها (سازگاری)
                                $residentDataForSms = [
                                    'id' => $residentData['resident_id'] ?? null,
                                    'db_id' => $residentDbId,
                                    'resident_id' => $residentData['resident_id'] ?? null,
                                    'resident_name' => $residentData['resident_name'] ?? '',
                                    'name' => $residentData['resident_name'] ?? '',
                                    'phone' => $residentData['phone'] ?? '',
                                    'unit_id' => $residentData['unit_id'] ?? null,
                                    'unit_name' => $residentData['unit_name'] ?? '',
                                    'room_id' => $residentData['room_id'] ?? null,
                                    'room_name' => $residentData['room_name'] ?? '',
                                    'bed_id' => $residentData['bed_id'] ?? null,
                                    'bed_name' => $residentData['bed_name'] ?? '',
                                ];
                                
                                // استخراج متغیرها از متن الگو (با استفاده از داده‌های دیتابیس)
                                $variables = $this->extractPatternVariables($pattern->text, $residentDataForSms, $residentDataForVariables, $report);
                                
                                // علامت‌گذاری که پیامک در حال ارسال است (برای جلوگیری از ارسال دوبار توسط Event Listener)
                                $residentReport->update(['has_been_sent' => true]);
                                
                                // دریافت شماره فرستنده
                                $senderNumber = SenderNumber::getActivePatternNumbers()->first();
                                $senderNumberValue = $senderNumber ? $senderNumber->number : null;
                                $apiKey = $senderNumber ? $senderNumber->api_key : null;
                                
                                // اگر API Key از sender number دریافت نشد، از جدول api_keys استفاده می‌کنیم
                                if (empty($apiKey)) {
                                    $dbConsoleKey = \App\Models\ApiKey::getKeyValue('console_api_key');
                                    $dbApiKey = \App\Models\ApiKey::getKeyValue('api key');
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
                                    'report_id' => $reportId,
                                    'pattern_id' => $pattern->id,
                                    'is_pattern' => true,
                                    'pattern_variables' => implode(';', $variables),
                                    'resident_id' => $residentDbId,
                                    'resident_name' => $residentData['resident_name'] ?? '',
                                    'phone' => $residentData['phone'] ?? '',
                                    'title' => $pattern->title,
                                    'description' => $pattern->text,
                                    'status' => 'pending',
                                ]);
                                
                                // ارسال پیامک با الگو - استفاده از sendByBaseNumber2 (مشابه PatternManual)
                                $melipayamakService = new MelipayamakService();
                                $bodyId = (int)$pattern->pattern_code;
                                
                                $result = $melipayamakService->sendByBaseNumber2(
                                    $residentData['phone'],
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
                                        'rec_id' => $result['rec_id'] ?? null,
                                        'api_response' => $result['api_response'] ?? null,
                                        'raw_response' => $result['raw_response'] ?? null,
                                    ]);
                                } else {
                                    $smsMessageResident->update([
                                        'status' => 'failed',
                                        'error_message' => $result['message'] ?? 'خطا در ارسال',
                                        'response_code' => $result['response_code'] ?? null,
                                        'rec_id' => $result['rec_id'] ?? null,
                                        'api_response' => $result['api_response'] ?? null,
                                        'raw_response' => $result['raw_response'] ?? null,
                                    ]);
                                }
                                
                                // ذخیره نتیجه برای نمایش
                                $smsResult = $smsMessageResident;
                                
                            } catch (\Exception $e) {
                                Log::error('Error sending SMS in Units (Group)', [
                                    'report_id' => $reportId,
                                    'resident_id' => $residentDbId,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString(),
                                ]);
                            }
                        }
                    }

                    // لاگ برای بررسی ذخیره‌سازی
                    \Log::info('گزارش گروهی در دیتابیس ذخیره شد', [
                        'resident_report_id' => $residentReport->id,
                        'report_id' => $reportId,
                        'resident_id' => $residentReport->resident_id,
                        'resident_db_id' => $residentDbId,
                        'resident_name' => $residentReport->resident_name,
                        'created_at' => $residentReport->created_at,
                        'sms_result_found' => $smsResult ? 'yes' : 'no',
                        'sms_result_id' => $smsResult ? $smsResult->id : null,
                    ]);

                    // بررسی اینکه آیا رکورد واقعاً در دیتابیس ذخیره شده است
                    $existsInDb = \App\Models\ResidentReport::where('id', $residentReport->id)->exists();
                    if (!$existsInDb) {
                        throw new \Exception('رکورد در دیتابیس ذخیره نشد!');
                    }

                    // خواندن رکورد از دیتابیس برای نمایش پاسخ
                    $submittedReport = \App\Models\ResidentReport::with(['report', 'report.category'])
                        ->find($residentReport->id);
                    
                    if (!$submittedReport) {
                        throw new \Exception('رکورد از دیتابیس خوانده نشد!');
                    }
                    
                    $submittedReports[] = [
                        'id' => $submittedReport->id,
                        'report_id' => $submittedReport->report_id,
                        'report_title' => $submittedReport->report->title ?? 'نامشخص',
                        'category_name' => $submittedReport->report->category->name ?? 'بدون دسته',
                        'resident_name' => $submittedReport->resident_name,
                        'phone' => $submittedReport->phone,
                        'unit_name' => $submittedReport->unit_name,
                        'room_name' => $submittedReport->room_name,
                        'bed_name' => $submittedReport->bed_name,
                        'notes' => $submittedReport->notes,
                        'created_at' => $submittedReport->created_at ? $submittedReport->created_at->toDateTimeString() : null,
                        'all_data' => $this->prepareArrayForJson($submittedReport), // تمام داده‌های رکورد
                        'sms_result' => $smsResult ? [
                            'status' => $smsResult->status,
                            'success' => $smsResult->status === 'sent',
                            'message' => $smsResult->status === 'sent' 
                                ? ($smsResult->rec_id ? 'پیامک با موفقیت ارسال شد (RecId: ' . $smsResult->rec_id . ')' : 'پیامک با موفقیت ارسال شد')
                                : ($smsResult->error_message ?? 'خطا در ارسال'),
                            'response_code' => $smsResult->response_code,
                            'rec_id' => $smsResult->rec_id ?? null,
                            'error_message' => $smsResult->error_message,
                            'api_response' => $smsResult->api_response,
                            'raw_response' => $smsResult->raw_response,
                            'sent_at' => $smsResult->sent_at ? $smsResult->sent_at->toDateTimeString() : null,
                        ] : null,
                    ];
                    
                    // ذخیره پاسخ SMS برای نمایش در modal
                    if ($smsResult) {
                        $smsResultArray = [
                            'status' => $smsResult->status ?? 'pending',
                            'success' => ($smsResult->status ?? 'pending') === 'sent',
                            'message' => ($smsResult->status ?? 'pending') === 'sent' 
                                ? ($smsResult->rec_id ? 'پیامک با موفقیت ارسال شد (RecId: ' . $smsResult->rec_id . ')' : 'پیامک با موفقیت ارسال شد')
                                : ($smsResult->error_message ?? 'خطا در ارسال'),
                            'response_code' => $smsResult->response_code ?? null,
                            'rec_id' => $smsResult->rec_id ?? null,
                            'error_message' => $smsResult->error_message ?? null,
                            'api_response' => $smsResult->api_response ?? null,
                            'raw_response' => $smsResult->raw_response ?? null,
                            'sent_at' => $smsResult->sent_at ? $smsResult->sent_at->toDateTimeString() : null,
                            // پاسخ کامل API برای نمایش دقیق
                            'full_api_response' => $smsResult->api_response ? (is_string($smsResult->api_response) ? json_decode($smsResult->api_response, true) : $smsResult->api_response) : null,
                            'full_raw_response' => $smsResult->raw_response ?? null,
                        ];
                        
                        $this->smsResponses[] = [
                            'report_id' => $reportId,
                            'report_title' => $submittedReport->report->title ?? 'نامشخص',
                            'resident_name' => $submittedReport->resident_name,
                            'phone' => $submittedReport->phone,
                            'sms_result' => $smsResultArray,
                        ];
                        
                        // نمایش toast notification برای پاسخ ملی پیامک
                        $smsToastType = $smsResultArray['success'] ? 'success' : 'error';
                        $smsToastTitle = $smsResultArray['success'] ? 'پیامک ارسال شد' : 'خطا در ارسال پیامک';
                        $smsToastMessage = $smsResultArray['message'] ?? '';
                        if (!empty($smsResultArray['rec_id'])) {
                            $smsToastMessage .= ' (RecId: ' . $smsResultArray['rec_id'] . ')';
                        }
                        
                        $this->dispatch('showToast', [
                            'type' => $smsToastType,
                            'title' => $smsToastTitle,
                            'message' => $smsToastMessage,
                            'duration' => 0, // بسته نشود تا زمانی که کاربر روی ضربدر کلیک کند
                        ]);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $residentName = $residentData['resident_name'] ?? 'نامشخص';
                    $errors[] = [
                        'report_id' => $reportId,
                        'resident_name' => $residentName,
                        'error' => $e->getMessage(),
                        'error_details' => [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'code' => $e->getCode(),
                        ]
                    ];
                }
            }
        }

        // ذخیره نتایج برای نمایش
        $this->lastSubmittedReports = $submittedReports;
        $this->showSubmissionResult = true;
        
        // اسکرول به کارت نتایج بعد از بسته شدن مودال
        $this->dispatch('scroll-to-results');
        
        // باز کردن modal پاسخ SMS اگر پیامکی ارسال شده باشد
        if (!empty($this->smsResponses)) {
            $this->showSmsResponseModal = true;
        }

        if ($failedCount === 0) {
            $this->selectedResidents = [];
        }

        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors,
            'submitted_reports' => $submittedReports
        ];
    }

    public function closeModal()
    {
        $this->showReportModal = false;
        $this->selectedReports = [];
        $this->notes = '';
        $this->currentResident = null;
        $this->currentRoom = null;
        $this->reportModalLoading = false;
        $this->databaseResponse = null; // پاک کردن پاسخ دیتابیس
        $this->reportCheckError = null; // پاک کردن پیام خطا
    }
    
    public function closeSmsResponseModal()
    {
        $this->showSmsResponseModal = false;
        $this->smsResponses = [];
    }

    public function closeSubmissionResult()
    {
        $this->showSubmissionResult = false;
        $this->lastSubmittedReports = [];
    }

    private function prepareArrayForJson($model)
    {
        $data = $model->toArray();
        
        // تبدیل Carbon instances به رشته
        foreach ($data as $key => $value) {
            if ($value instanceof \Carbon\Carbon) {
                $data[$key] = $value->toDateTimeString();
            } elseif (is_array($value)) {
                $data[$key] = $this->convertCarbonInArray($value);
            }
        }
        
        // تبدیل روابط به آرایه
        if ($model->relationLoaded('report')) {
            $data['report'] = $this->convertModelToArray($model->report);
        }
        if ($model->relationLoaded('report') && $model->report && $model->report->relationLoaded('category')) {
            $data['report']['category'] = $this->convertModelToArray($model->report->category);
        }
        
        return $data;
    }

    private function convertCarbonInArray($array)
    {
        foreach ($array as $key => $value) {
            if ($value instanceof \Carbon\Carbon) {
                $array[$key] = $value->toDateTimeString();
            } elseif (is_array($value)) {
                $array[$key] = $this->convertCarbonInArray($value);
            }
        }
        return $array;
    }

    private function convertModelToArray($model)
    {
        if (!$model) {
            return null;
        }
        
        $data = $model->toArray();
        foreach ($data as $key => $value) {
            if ($value instanceof \Carbon\Carbon) {
                $data[$key] = $value->toDateTimeString();
            } elseif (is_array($value)) {
                $data[$key] = $this->convertCarbonInArray($value);
            }
        }
        return $data;
    }


    public function toggleSelectResident($key, $resident, $bed, $unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];

        if (isset($this->selectedResidents[$key])) {
            unset($this->selectedResidents[$key]);
        } else {
            $this->selectedResidents[$key] = [
                'resident_id' => $resident['id'],
                'resident_name' => $resident['full_name'],
                'phone' => $resident['phone'],
                'job' => $resident['job'] ?? null,
                'bed_id' => $bed['id'],
                'bed_name' => $bed['name'],
                'unit_id' => $unit['unit']['id'],
                'unit_name' => $unit['unit']['name'],
                'room_id' => $room['id'],
                'room_name' => $room['name']
            ];
        }
    }

    public function selectAllInRoom($unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];
        $allSelected = true;

        foreach ($room['beds'] as $bed) {
            if ($bed['resident']) {
                $key = $unitIndex . '_' . $roomIndex . '_' . $bed['id'];
                if (!isset($this->selectedResidents[$key])) {
                    $allSelected = false;
                    break;
                }
            }
        }

        foreach ($room['beds'] as $bed) {
            if ($bed['resident']) {
                $key = $unitIndex . '_' . $roomIndex . '_' . $bed['id'];
                if ($allSelected) {
                    unset($this->selectedResidents[$key]);
                } else {
                    $this->selectedResidents[$key] = [
                        'resident_id' => $bed['resident']['id'],
                        'resident_name' => $bed['resident']['full_name'],
                        'phone' => $bed['resident']['phone'],
                        'job' => $bed['resident']['job'] ?? null,
                        'bed_id' => $bed['id'],
                        'bed_name' => $bed['name'],
                        'unit_id' => $unit['unit']['id'],
                        'unit_name' => $unit['unit']['name'],
                        'room_id' => $room['id'],
                        'room_name' => $room['name']
                    ];
                }
            }
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

        if ($this->filterEmptyBeds) {
            $filteredUnits = array_filter($filteredUnits, function ($unit) {
                foreach ($unit['rooms'] as $room) {
                    foreach ($room['beds'] as $bed) {
                        if ($bed['resident']) {
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
     * بررسی اینکه آیا همه گزارش‌های قبلی اقامت‌گر(های) انتخاب شده چک شده‌اند یا نه
     */
    private function checkAllReportsAreChecked()
    {
        if ($this->reportType === 'individual') {
            // برای گزارش فردی
            if (empty($this->currentResident) || empty($this->currentResident['id'])) {
                return ['has_unchecked' => false, 'message' => ''];
            }

            // پیدا کردن resident از جدول residents
            $resident = \App\Models\Resident::where('resident_id', $this->currentResident['id'])->first();
            if (!$resident) {
                return ['has_unchecked' => false, 'message' => ''];
            }

            // بررسی گزارش‌های چک نشده
            $uncheckedCount = \App\Models\ResidentReport::whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->where('resident_id', $resident->id)
            ->where('is_checked', false)
            ->count();

            if ($uncheckedCount > 0) {
                return [
                    'has_unchecked' => true,
                    'message' => "برای اقامت‌گر {$this->currentResident['name']}، {$uncheckedCount} گزارش چک نشده وجود دارد."
                ];
            }
        } else {
            // برای گزارش گروهی
            if (empty($this->selectedResidents)) {
                return ['has_unchecked' => false, 'message' => ''];
            }

            $uncheckedResidents = [];
            foreach ($this->selectedResidents as $residentData) {
                if (empty($residentData['resident_id'])) {
                    continue;
                }

                // پیدا کردن resident از جدول residents
                $resident = \App\Models\Resident::where('resident_id', $residentData['resident_id'])->first();
                if (!$resident) {
                    continue;
                }

                // بررسی گزارش‌های چک نشده
                $uncheckedCount = \App\Models\ResidentReport::whereHas('report', function($q) {
                    $q->where('category_id', 1); // دسته‌بندی تخلف
                })
                ->where('resident_id', $resident->id)
                ->where('is_checked', false)
                ->count();

                if ($uncheckedCount > 0) {
                    $uncheckedResidents[] = [
                        'name' => $residentData['resident_name'] ?? 'نامشخص',
                        'count' => $uncheckedCount
                    ];
                }
            }

            if (!empty($uncheckedResidents)) {
                $messages = [];
                foreach ($uncheckedResidents as $item) {
                    $messages[] = "{$item['name']}: {$item['count']} گزارش چک نشده";
                }
                return [
                    'has_unchecked' => true,
                    'message' => implode(' | ', $messages)
                ];
            }
        }

        return ['has_unchecked' => false, 'message' => ''];
    }

    /**
     * استخراج و جایگزینی متغیرها در الگو (مشابه GroupSms)
     */
    protected function extractPatternVariables($patternText, $residentData, $residentDataFromDb = null, $report = null)
    {
        // پیدا کردن تمام متغیرها در الگو (مثل {0}, {1}, {2})
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[1])) {
            return []; // اگر متغیری وجود نداشت
        }

        // استفاده از داده‌های دیتابیس اگر موجود باشد، در غیر این صورت از API
        if ($residentDataFromDb) {
            // تبدیل داده‌های دیتابیس به ساختار مورد نیاز
            $residentDataForVariables = [
                'resident' => [
                    'id' => $residentDataFromDb['id'] ?? $residentDataFromDb['resident_id'] ?? null,
                    'resident_id' => $residentDataFromDb['resident_id'] ?? null,
                    // نگه داشتن نام فیلدهای واقعی دیتابیس
                    'resident_full_name' => $residentDataFromDb['resident_full_name'] ?? '',
                    'resident_phone' => $residentDataFromDb['resident_phone'] ?? '',
                    'resident_age' => $residentDataFromDb['resident_age'] ?? '',
                    'resident_job' => $residentDataFromDb['resident_job'] ?? '',
                    'contract_payment_date_jalali' => $residentDataFromDb['contract_payment_date_jalali'] ?? '',
                    'contract_start_date_jalali' => $residentDataFromDb['contract_start_date_jalali'] ?? '',
                    'contract_end_date_jalali' => $residentDataFromDb['contract_end_date_jalali'] ?? '',
                    // همچنین نام‌های جایگزین برای سازگاری
                    'full_name' => $residentDataFromDb['resident_full_name'] ?? '',
                    'name' => $residentDataFromDb['resident_full_name'] ?? '',
                    'phone' => $residentDataFromDb['resident_phone'] ?? '',
                    'national_id' => $residentDataFromDb['national_id'] ?? $residentDataFromDb['national_code'] ?? '',
                    'national_code' => $residentDataFromDb['national_id'] ?? $residentDataFromDb['national_code'] ?? '',
                    'payment_date_jalali' => $residentDataFromDb['contract_payment_date_jalali'] ?? '',
                ],
                'unit' => [
                    'id' => $residentDataFromDb['unit_id'] ?? null,
                    'name' => $residentDataFromDb['unit_name'] ?? '',
                    'code' => $residentDataFromDb['unit_code'] ?? '',
                ],
                'room' => [
                    'id' => $residentDataFromDb['room_id'] ?? null,
                    'name' => $residentDataFromDb['room_name'] ?? '',
                    'code' => $residentDataFromDb['room_code'] ?? '',
                ],
                'bed' => [
                    'id' => $residentDataFromDb['bed_id'] ?? null,
                    'name' => $residentDataFromDb['bed_name'] ?? '',
                    'code' => $residentDataFromDb['bed_code'] ?? '',
                ],
            ];
        } else {
            // دریافت اطلاعات کامل resident از API یا استفاده از داده‌های موجود
            $residentDataForVariables = $this->getResidentDataFromDb($residentData);
        }
        
        // دریافت اطلاعات گزارش
        $reportData = null;
        if ($report) {
            $reportData = [
                'title' => $report->title,
                'description' => $report->description,
                'category_name' => $report->category->name ?? '',
                'negative_score' => $report->negative_score,
                'type' => $report->type ?? 'violation',
            ];
        }

        // بارگذاری متغیرها از دیتابیس
        $variables = PatternVariable::where('is_active', true)
            ->get()
            ->keyBy('code'); // کلید بر اساس کد (مثل {0}, {1})

        $result = [];
        $usedIndices = array_unique(array_map('intval', $matches[1]));
        sort($usedIndices); // مرتب‌سازی بر اساس ترتیب در الگو

        // پیدا کردن بزرگترین index برای ساخت آرایه کامل
        $maxIndex = !empty($usedIndices) ? max($usedIndices) : -1;
        
        // ساخت آرایه کامل از 0 تا maxIndex
        // API ملی پیامک انتظار دارد که متغیرها به ترتیب {0}, {1}, {2}, ... باشند
        // حتی اگر در الگو {0}, {2}, {3} باشد، باید آرایه [value0, '', value2, value3] باشد
        for ($i = 0; $i <= $maxIndex; $i++) {
            $code = '{' . $i . '}';
            $variable = $variables->get($code);
            
            if ($variable) {
                $value = $this->getVariableValue($variable, $residentDataForVariables, $reportData);
                
                // اطمینان از اینکه value یک رشته است
                if (!is_string($value)) {
                    $value = (string)$value;
                }
                
                $result[] = $value;
            } else {
                // اگر متغیر در دیتابیس تعریف نشده یا در الگو استفاده نشده، مقدار خالی می‌گذاریم
                // این برای متغیرهای جا افتاده (مثل {1} در الگوی {0}, {2}, {3}) ضروری است
                $result[] = ''; // مقدار خالی برای متغیرهای جا افتاده
            }
        }

        return $result;
    }

    protected function getResidentDataFromDb($residentData)
    {
        // اگر resident_id وجود دارد، از دیتابیس بخوان
        $residentDb = null;
        if (!empty($residentData['id']) || !empty($residentData['resident_id'])) {
            $residentId = $residentData['id'] ?? $residentData['resident_id'];
            $residentDb = \App\Models\Resident::where('resident_id', $residentId)->first();
        }
        
        if ($residentDb) {
            // استفاده از داده‌های دیتابیس
            return [
                'resident' => [
                    'id' => $residentDb->id,
                    'resident_id' => $residentDb->resident_id,
                    'resident_full_name' => $residentDb->resident_full_name,
                    'resident_phone' => $residentDb->resident_phone,
                    'resident_age' => $residentDb->resident_age,
                    'resident_job' => $residentDb->resident_job,
                    'contract_payment_date_jalali' => $residentDb->contract_payment_date_jalali,
                    'contract_start_date_jalali' => $residentDb->contract_start_date_jalali,
                    'contract_end_date_jalali' => $residentDb->contract_end_date_jalali,
                    // همچنین نام‌های جایگزین برای سازگاری
                    'full_name' => $residentDb->resident_full_name,
                    'name' => $residentDb->resident_full_name,
                    'phone' => $residentDb->resident_phone,
                ],
                'unit' => [
                    'id' => $residentDb->unit_id,
                    'name' => $residentDb->unit_name,
                    'code' => $residentDb->unit_code,
                ],
                'room' => [
                    'id' => $residentDb->room_id,
                    'name' => $residentDb->room_name,
                    'code' => $residentDb->room_code,
                ],
                'bed' => [
                    'id' => $residentDb->bed_id,
                    'name' => $residentDb->bed_name,
                    'code' => $residentDb->bed_code,
                ],
            ];
        }
        
        // در صورت عدم وجود در دیتابیس، از داده‌های موجود استفاده می‌کنیم
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
        $field = $variable->table_field ?? '';
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
            // فیلدهای عمومی
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

        return view('livewire.residents.units', [
            'filteredUnits' => $filteredUnits
        ]);
    }
}
