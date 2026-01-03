<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\Report;
use App\Models\Category;
use App\Models\Resident;
use App\Models\ResidentReport;
use App\Services\ResidentService;

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
    public $selectedReports = [];
    public $notes = '';
    public $expandedUnits = [];
    public $reportModalLoading = false;
    public $lastSubmittedReports = []; // آخرین گزارش‌های ثبت شده
    public $showSubmissionResult = false; // نمایش نتیجه ثبت
    public $databaseResponse = null; // پاسخ دیتابیس برای نمایش در مودال
    public $reportCheckError = null; // پیام خطا برای چک نشدن همه گزارش‌ها

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
            } else {
                // ذخیره پاسخ دیتابیس برای نمایش در مودال
                $this->databaseResponse = [
                    'success' => true,
                    'message' => "{$successCount} گزارش با موفقیت در دیتابیس ثبت شد.",
                    'reports' => $result['submitted_reports'] ?? []
                ];
                
                // لاگ پاسخ دیتابیس در کنسول
                $this->dispatch('logDatabaseResponse', [
                    'success' => true,
                    'count' => $successCount,
                    'reports' => $result['submitted_reports'] ?? []
                ]);
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

                // تاخیر برای اطمینان از اجرای Event و Listener
                // چند بار تلاش می‌کنیم تا SMS result را پیدا کنیم
                $smsResult = null;
                $maxAttempts = 5;
                $attemptDelay = 300000; // 0.3 ثانیه
                
                for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                    if ($attempt > 0) {
                        usleep($attemptDelay);
                    }
                    
                    // دریافت پاسخ واقعی از ملی پیامک از جدول sms_message_residents
                    $smsResult = \App\Models\SmsMessageResident::where('report_id', $reportId)
                        ->where(function($query) use ($residentReport, $residentDbId) {
                            // جستجو بر اساس resident_id از جدول residents (id واقعی)
                            if ($residentDbId) {
                                $query->where('resident_id', $residentDbId);
                            }
                            // همچنین بر اساس resident_id از API (resident_id از resident_reports)
                            if ($residentReport->resident_id) {
                                $query->orWhere(function($q) use ($residentReport) {
                                    // اگر resident_id در sms_message_residents همان resident_id از API است
                                    $q->where('resident_id', $residentReport->resident_id);
                                });
                            }
                        })
                        ->where('created_at', '>=', now()->subMinutes(5)) // 5 دقیقه گذشته
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($smsResult) {
                        break; // پیدا شد، از حلقه خارج می‌شویم
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
                        'message' => $smsResult->status === 'sent' ? 'پیامک با موفقیت ارسال شد' : ($smsResult->error_message ?? 'خطا در ارسال'),
                        'response_code' => $smsResult->response_code,
                        'rec_id' => $smsResult->rec_id ?? null,
                        'error_message' => $smsResult->error_message,
                        'api_response' => $smsResult->api_response,
                        'raw_response' => $smsResult->raw_response,
                        'sent_at' => $smsResult->sent_at ? $smsResult->sent_at->toDateTimeString() : null,
                    ] : null,
                ];

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

                    // تاخیر برای اطمینان از اجرای Event و Listener
                    // چند بار تلاش می‌کنیم تا SMS result را پیدا کنیم
                    $smsResult = null;
                    $maxAttempts = 5;
                    $attemptDelay = 300000; // 0.3 ثانیه
                    
                    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                        if ($attempt > 0) {
                            usleep($attemptDelay);
                        }
                        
                        // دریافت پاسخ واقعی از ملی پیامک از جدول sms_message_residents
                        // در SendViolationSms listener، resident_id همان id از جدول residents است
                        $query = \App\Models\SmsMessageResident::where('report_id', $reportId)
                            ->where('created_at', '>=', now()->subMinutes(5)); // 5 دقیقه گذشته
                        
                        // جستجو بر اساس resident_id (id از جدول residents)
                        if ($residentDbId) {
                            $query->where('resident_id', $residentDbId);
                        }
                        
                        $smsResult = $query->orderBy('created_at', 'desc')->first();
                        
                        // اگر پیدا نشد، با phone هم جستجو کنیم
                        if (!$smsResult && $residentReport->phone) {
                            \Log::info('Units - Trying to find SMS result by phone', [
                                'report_id' => $reportId,
                                'phone' => $residentReport->phone,
                            ]);
                            
                            $smsResult = \App\Models\SmsMessageResident::where('report_id', $reportId)
                                ->where('phone', $residentReport->phone)
                                ->where('created_at', '>=', now()->subMinutes(5))
                                ->orderBy('created_at', 'desc')
                                ->first();
                        }
                        
                        // لاگ برای دیباگ
                        if ($smsResult) {
                            \Log::info('Units - SMS result found', [
                                'sms_id' => $smsResult->id,
                                'status' => $smsResult->status,
                                'response_code' => $smsResult->response_code,
                                'has_api_response' => !empty($smsResult->api_response),
                                'has_raw_response' => !empty($smsResult->raw_response),
                            ]);
                        } else {
                            \Log::warning('Units - SMS result not found', [
                                'report_id' => $reportId,
                                'resident_db_id' => $residentDbId,
                                'resident_report_id' => $residentReport->id,
                                'phone' => $residentReport->phone,
                                'attempt' => $attempt + 1,
                            ]);
                        }
                        
                        if ($smsResult) {
                            break; // پیدا شد، از حلقه خارج می‌شویم
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
                            'message' => $smsResult->status === 'sent' ? 'پیامک با موفقیت ارسال شد' : ($smsResult->error_message ?? 'خطا در ارسال'),
                            'response_code' => $smsResult->response_code,
                            'error_message' => $smsResult->error_message,
                            'api_response' => $smsResult->api_response,
                            'raw_response' => $smsResult->raw_response,
                            'sent_at' => $smsResult->sent_at ? $smsResult->sent_at->toDateTimeString() : null,
                        ] : null,
                    ];

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

    public function render()
    {
        $filteredUnits = $this->getFilteredUnits();

        return view('livewire.residents.units', [
            'filteredUnits' => $filteredUnits
        ]);
    }
}
