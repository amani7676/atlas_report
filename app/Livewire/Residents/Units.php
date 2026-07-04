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
    public $selectedCategoryId = null; // دسته‌بندی انتخاب شده
    public $selectedReportId = null; // گزارش انتخاب شده (مقدار تکی)
    public $filteredReports = []; // گزارش‌های فیلتر شده بر اساس دسته‌بندی
    public $notes = '';
    public $description = '';
    public $expandedUnits = [];
    public $reportModalLoading = false;
    public $lastSubmittedReports = []; // آخرین گزارش‌های ثبت شده
    public $showSubmissionResult = false; // نمایش نتیجه ثبت
    public $databaseResponse = null; // پاسخ دیتابیس برای نمایش در مودال
    public $reportCheckError = null; // پیام خطا برای چک نشدن همه گزارش‌ها
    public $showSmsResponseModal = false; // نمایش modal پاسخ SMS
    public $smsResponses = []; // پاسخ‌های SMS برای نمایش در modal
    public $patternMessage = null; // پیام الگو با مقداردهی کدها
    public $searchResidentId = null; // ID اقامت‌گر پیدا شده برای highlight

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

    /**
     * وقتی جستجو تغییر می‌کند، موقعیت اقامت‌گر را پیدا کن
     */
    public function updatedSearch($value)
    {
        if (!empty($value)) {
            $this->findResidentPosition($value);
        } else {
            $this->searchResidentId = null;
        }
    }

    /**
     * پیدا کردن موقعیت اقامت‌گر بر اساس جستجو
     */
    private function findResidentPosition($searchTerm)
    {
        $searchTerm = strtolower($searchTerm);

        foreach ($this->units as $unitIndex => $unit) {
            foreach ($unit['rooms'] as $roomIndex => $room) {
                foreach ($room['beds'] as $bed) {
                    if ($bed['resident'] && (
                        strpos(strtolower($bed['resident']['full_name']), $searchTerm) !== false ||
                        strpos(strtolower($bed['resident']['phone']), $searchTerm) !== false
                    )) {
                        // اقامت‌گر پیدا شد
                        $this->searchResidentId = $bed['resident']['id'];

                        // انتقال این واحد به ابتدای لیست
                        $foundUnit = $this->units[$unitIndex];
                        array_splice($this->units, $unitIndex, 1);
                        array_unshift($this->units, $foundUnit);

                        // باز کردن واحد اگر بسته است
                        $this->expandedUnits = [0];

                        return;
                    }
                }
            }
        }

        $this->searchResidentId = null;
    }

    public function mount()
    {
        Log::info('Units::mount - Component mounting');
        $this->loadUnits();
        $this->loadReportData();
        Log::info('Units::mount - Component mounted successfully');
    }

    public function loadUnits()
    {
        $this->loading = true;
        $this->error = null;

        try {
            Log::info('Units::loadUnits - Starting to load units');
            
            $residentService = new ResidentService();
            $this->units = $residentService->getAllResidents();
            
            Log::info('Units::loadUnits - Units loaded successfully', [
                'units_count' => count($this->units)
            ]);
            
            $this->sortData();
            
            Log::info('Units::loadUnits - Data sorted successfully');
        } catch (\Exception $e) {
            Log::error('Units::loadUnits - Error loading units', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error = 'خطا در دریافت اطلاعات از دیتابیس: ' . $e->getMessage();
            $this->units = $this->getSampleData();
            
            Log::info('Units::loadUnits - Using sample data');
        }

        $this->loading = false;
        
        Log::info('Units::loadUnits - Loading completed, loading set to false');
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

    /**
     * به‌روزرسانی گزارش‌های فیلتر شده بر اساس دسته‌بندی انتخاب شده
     */
    public function updatedSelectedCategoryId($value)
    {
        $this->selectedReports = []; // پاک کردن گزارش‌های انتخاب شده قبلی
        $this->selectedReportId = null; // پاک کردن گزارش انتخاب شده
        
        if ($value) {
            $category = collect($this->categories)->firstWhere('id', $value);
            if ($category) {
                $this->filteredReports = $category['reports'] ?? [];
            }
        } else {
            $this->filteredReports = [];
        }
    }

    /**
     * به‌روزرسانی گزارش انتخاب شده
     */
    public function updatedSelectedReportId($value)
    {
        if ($value) {
            $this->selectedReports = [$value]; // برای سازگاری با ساختار موجود
        } else {
            $this->selectedReports = [];
        }
        
        // وقتی گزارش تغییر می‌کند، به صورت خودکار پیام الگو را محاسبه کن
        $this->patternMessage = $this->calculatePatternMessage();
    }

    public function updatedCurrentResident()
    {
        // وقتی اقامت‌گر تغییر می‌کند، پیام الگو را به‌روز کن
        $this->patternMessage = $this->calculatePatternMessage();
    }

    public function openIndividualReport($resident, $bed, $unitIndex, $roomIndex)
    {
        Log::info('Units::openIndividualReport - Opening report modal', [
            'unitIndex' => $unitIndex,
            'roomIndex' => $roomIndex,
            'resident_id' => $resident['id'] ?? 'N/A',
            'resident_name' => $resident['full_name'] ?? 'N/A',
            'total_units' => count($this->units)
        ]);

        // بررسی وجود واحد
        if (!isset($this->units[$unitIndex])) {
            Log::error('Units::openIndividualReport - Unit not found', [
                'unitIndex' => $unitIndex,
                'available_units' => array_keys($this->units)
            ]);
            return;
        }

        $unit = $this->units[$unitIndex];
        
        // بررسی وجود اتاق
        if (!isset($unit['rooms'][$roomIndex])) {
            Log::error('Units::openIndividualReport - Room not found', [
                'unitIndex' => $unitIndex,
                'roomIndex' => $roomIndex,
                'available_rooms' => array_keys($unit['rooms'])
            ]);
            return;
        }

        $room = $unit['rooms'][$roomIndex];

        Log::info('Units::openIndividualReport - Unit and room found', [
            'unit_name' => $unit['unit']['name'] ?? 'N/A',
            'room_name' => $room['name'] ?? 'N/A'
        ]);

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

        Log::info('Units::openIndividualReport - Current resident set', [
            'current_resident' => $this->currentResident
        ]);

        $this->loadReportData();
        $this->selectedReports = [];
        $this->selectedCategoryId = null;
        $this->selectedReportId = null;
        $this->filteredReports = [];
        $this->notes = '';
        $this->showReportModal = true;
        $this->dispatch('modal-opened');

        Log::info('Units::openIndividualReport - Modal opened successfully');
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
        $this->selectedCategoryId = null;
        $this->selectedReportId = null;
        $this->filteredReports = [];
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

    /**
     * محاسبه مجموع امتیاز منفی تخلفات یک اقامت‌گر
     */
    public function getViolationReportsCount($residentId)
    {
        if (!$residentId) {
            Log::warning('getViolationReportsCount: residentId is empty');
            return 0;
        }
        
        Log::info('getViolationReportsCount: Processing resident', [
            'residentId' => $residentId
        ]);
        
        // دریافت گزارش‌های مستثنی شده از تنظیمات
        $excludedReportIds = $this->getExcludedReports();
        
        // محاسبه مجموع امتیاز منفی تخلفات (category_id = 1)
        // مستقیماً از resident_id که از API آمده استفاده می‌کنیم
        $query = ResidentReport::where('resident_id', $residentId) // resident_id از API
            ->whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id');
            
        // حذف گزارش‌های مستثنی شده
        if (!empty($excludedReportIds)) {
            $query->whereNotIn('reports.id', $excludedReportIds);
        }
            
        Log::info('getViolationReportsCount: Query built', [
            'sql' => $query->toSql(),
            'residentId' => $residentId,
            'excludedReports' => $excludedReportIds
        ]);
        
        $totalScore = $query->sum('reports.negative_score');
        
        Log::info('getViolationReportsCount: Result', [
            'residentId' => $residentId,
            'totalScore' => $totalScore,
            'count' => $query->count(),
            'excludedReports' => $excludedReportIds
        ]);
        
        return (int)($totalScore ?? 0);
    }
    
    /**
     * محاسبه مجموع امتیاز منفی تخلفات یک اقامت‌گر (فقط از گزارش‌های غیرمستثنی)
     * این متد برای کارت‌های زرد و قرمز استفاده می‌شود
     */
    public function getEligibleViolationScore($residentId)
    {
        if (!$residentId) {
            Log::warning('getEligibleViolationScore: residentId is empty');
            return 0;
        }
        
        Log::info('getEligibleViolationScore: Processing resident', [
            'residentId' => $residentId
        ]);
        
        // دریافت گزارش‌های مستثنی شده از تنظیمات
        $excludedReportIds = $this->getExcludedReports();
        
        // محاسبه مجموع امتیاز منفی تخلفات (category_id = 1)
        // فقط از گزارش‌های غیرمستثنی برای کارت‌ها
        $query = ResidentReport::where('resident_id', $residentId) // resident_id از API
            ->whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id');
            
        // حذف گزارش‌های مستثنی شده - فقط گزارش‌های واجد شرایط برای کارت
        if (!empty($excludedReportIds)) {
            $query->whereNotIn('reports.id', $excludedReportIds);
        }
            
        Log::info('getEligibleViolationScore: Query built', [
            'sql' => $query->toSql(),
            'residentId' => $residentId,
            'excludedReports' => $excludedReportIds
        ]);
        
        $totalScore = $query->sum('reports.negative_score');
        
        Log::info('getEligibleViolationScore: Result', [
            'residentId' => $residentId,
            'totalScore' => $totalScore,
            'count' => $query->count(),
            'excludedReports' => $excludedReportIds
        ]);
        
        return (int)($totalScore ?? 0);
    }
    
    /**
     * محاسبه مجموع امتیاز منفی تخلفات یک اقامت‌گر (بدون در نظر گرفتن مستثنی‌ها)
     * این متد برای نمایش امتیاز واقعی در کارت‌ها استفاده می‌شود
     */
    public function getTotalViolationScore($residentId)
    {
        if (!$residentId) {
            Log::warning('getTotalViolationScore: residentId is empty');
            return 0;
        }
        
        Log::info('getTotalViolationScore: Processing resident', [
            'residentId' => $residentId
        ]);
        
        // محاسبه مجموع امتیاز منفی تخلفات (category_id = 1)
        // بدون حذف گزارش‌های مستثنی شده - برای امتیاز مجموع واقعی
        $query = ResidentReport::where('resident_id', $residentId) // resident_id از API
            ->whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id');
            
        Log::info('getTotalViolationScore: Query built', [
            'sql' => $query->toSql(),
            'residentId' => $residentId
        ]);
        
        $totalScore = $query->sum('reports.negative_score');
        
        Log::info('getTotalViolationScore: Result', [
            'residentId' => $residentId,
            'totalScore' => $totalScore,
            'count' => $query->count()
        ]);
        
        return (int)($totalScore ?? 0);
    }
    
    /**
     * دریافت لیست گزارش‌های مستثنی شده از تنظیمات
     */
    private function getExcludedReports()
    {
        $excludedReportsConstant = \App\Models\Constant::where('key', 'excluded_reports')->first();
        if ($excludedReportsConstant && $excludedReportsConstant->value) {
            return json_decode($excludedReportsConstant->value, true) ?? [];
        }
        return [];
    }
    
    /**
     * تعیین رنگ کارت بر اساس امتیاز تخلفات و تنظیمات
     * استفاده از آستانه‌های تعریف شده در تنظیمات
     */
    public function getViolationCardColor($residentId)
    {
        // استفاده از مجموع امتیاز کل (با گزارش‌های مستثنی) برای تصمیم‌گیری کارت
        $totalScore = $this->getTotalViolationScore($residentId);
        
        // دریافت آستانه‌ها از تنظیمات
        $yellowThreshold = $this->getYellowCardThreshold();
        $redThreshold = $this->getRedCardThreshold();
        
        if ($totalScore == 0) {
            return [
                'bg' => '#10b981',
                'text' => '#ffffff',
                'border' => '#059669',
                'label' => 'بدون تخلف',
                'score' => $totalScore
            ];
        } elseif ($totalScore >= 1 && $totalScore < $yellowThreshold) {
            return [
                'bg' => '#f59e0b',
                'text' => '#ffffff',
                'border' => '#d97706',
                'label' => 'تخلف کم',
                'score' => $totalScore
            ];
        } elseif ($totalScore >= $yellowThreshold && $totalScore < $redThreshold) {
            return [
                'bg' => '#8b5cf6',
                'text' => '#ffffff',
                'border' => '#7c3aed',
                'label' => 'تخلف متوسط',
                'score' => $totalScore
            ];
        } else {
            return [
                'bg' => '#ef4444',
                'text' => '#ffffff',
                'border' => '#dc2626',
                'label' => 'تخلف زیاد',
                'score' => $totalScore
            ];
        }
    }
    
    /**
     * دریافت آستانه کارت زرد از تنظیمات
     */
    private function getYellowCardThreshold()
    {
        // دریافت از constants table
        $threshold = \App\Models\Constant::where('key', 'yellow_card_threshold')->first();
        return $threshold ? (int)$threshold->value : 15;
    }
    
    /**
     * دریافت آستانه کارت قرمز از تنظیمات
     */
    private function getRedCardThreshold()
    {
        // دریافت از constants table
        $threshold = \App\Models\Constant::where('key', 'red_card_threshold')->first();
        return $threshold ? (int)$threshold->value : 25;
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
        $this->selectedCategoryId = null;
        $this->selectedReportId = null;
        $this->filteredReports = [];
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

            // حذف تاخیر مصنوعی - ارسال SMS به صورت async انجام می‌شود

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
                    'message' => "{$successCount} گزارش با موفقیت در دیتابیس ثبت شد. پیامک‌ها در صف ارسال قرار گرفتند.",
                    'reports' => $result['submitted_reports'] ?? []
                ];
                
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

        // پیدا کردن ID واقعی resident در جدول residents (یک بار برای همه گزارش‌ها)
        $residentDbId = null;
        if (!empty($this->currentResident['id'])) {
            $resident = \App\Models\Resident::where('resident_id', $this->currentResident['id'])->first();
            $residentDbId = $resident ? $resident->id : null;
        }

        foreach ($this->selectedReports as $reportId) {
            try {
                // ایجاد رکورد در دیتابیس
                $residentReport = \App\Models\ResidentReport::create([
                    'report_id' => $reportId,
                    'resident_id' => $this->currentResident['id'], // استفاده از resident_id از API
                    'resident_name' => $this->currentResident['name'] ?? null,
                    'phone' => $this->currentResident['phone'] ?? null,
                    'unit_id' => $this->currentResident['unit_id'] ?? null,
                    'unit_name' => $this->currentResident['unit_name'] ?? null,
                    'room_id' => $this->currentResident['room_id'] ?? null,
                    'room_name' => $this->currentResident['room_name'] ?? null,
                    'bed_id' => $this->currentResident['bed_id'] ?? null,
                    'bed_name' => $this->currentResident['bed_name'] ?? null,
                    'notes' => $this->notes,
                    'description' => $this->description ?? null,
                ]);

                // ارسال پیامک الگویی به صورت queue (async)
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
                            // ساخت داده‌های resident برای استخراج متغیرها
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

                            // استخراج متغیرها از متن الگو
                            $variables = $this->extractPatternVariables($pattern->text, $residentData);

                            // ایجاد رکورد در sms_message_residents
                            $smsMessageResident = SmsMessageResident::create([
                                'sms_message_id' => null,
                                'report_id' => $reportId,
                                'pattern_id' => $pattern->id,
                                'is_pattern' => true,
                                'pattern_variables' => implode(';', $variables),
                                'resident_id' => $this->currentResident['id'],
                                'resident_name' => $this->currentResident['name'] ?? '',
                                'phone' => $this->currentResident['phone'] ?? '',
                                'title' => $pattern->title,
                                'description' => $pattern->text,
                                'status' => 'pending',
                            ]);

                            // ارسال پیامک به صورت queue (async)
                            dispatch(new \App\Jobs\SendPatternSmsJob(
                                $smsMessageResident->id,
                                $this->currentResident['phone'],
                                $pattern->pattern_code,
                                $variables
                            ));

                            // ذخیره نتیجه برای نمایش (pending چون async است)
                            $smsResult = $smsMessageResident;

                        } catch (\Exception $e) {
                            Log::error('Error queuing SMS in Units', [
                                'report_id' => $reportId,
                                'resident_id' => $residentDbId,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
                
                // ساخت آرایه sms_result برای نمایش (async - pending status)
                $smsResultArray = null;
                if ($smsResult) {
                    $smsResultArray = [
                        'status' => 'pending', // چون async است
                        'success' => null,
                        'message' => 'پیامک در صف ارسال قرار گرفت',
                        'response_code' => null,
                        'rec_id' => null,
                        'error_message' => null,
                        'api_response' => null,
                        'raw_response' => null,
                        'sent_at' => null,
                    ];
                }

                // خواندن رکورد از دیتابیس برای نمایش پاسخ
                $submittedReport = \App\Models\ResidentReport::with(['report', 'report.category'])
                    ->find($residentReport->id);

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
                    'sms_result' => $smsResultArray,
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
            // پیدا کردن ID واقعی resident در جدول residents (یک بار برای همه گزارش‌ها)
            $residentDbId = null;
            if (!empty($residentData['resident_id'])) {
                $resident = \App\Models\Resident::where('resident_id', $residentData['resident_id'])->first();
                $residentDbId = $resident ? $resident->id : null;
            }

            foreach ($this->selectedReports as $reportId) {
                try {
                    // ایجاد رکورد در دیتابیس
                    $residentReport = \App\Models\ResidentReport::create([
                        'report_id' => $reportId,
                        'resident_id' => $residentData['resident_id'],
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

                    // ارسال پیامک الگویی به صورت queue (async)
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
                                // ساخت داده‌های resident برای استخراج متغیرها
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

                                // استخراج متغیرها از متن الگو
                                $variables = $this->extractPatternVariables($pattern->text, $residentDataForSms);

                                // ایجاد رکورد در sms_message_residents
                                $smsMessageResident = SmsMessageResident::create([
                                    'sms_message_id' => null,
                                    'report_id' => $reportId,
                                    'pattern_id' => $pattern->id,
                                    'is_pattern' => true,
                                    'pattern_variables' => implode(';', $variables),
                                    'resident_id' => $residentData['resident_id'],
                                    'resident_name' => $residentData['resident_name'] ?? '',
                                    'phone' => $residentData['phone'] ?? '',
                                    'title' => $pattern->title,
                                    'description' => $pattern->text,
                                    'status' => 'pending',
                                ]);

                                // ارسال پیامک به صورت queue (async)
                                dispatch(new \App\Jobs\SendPatternSmsJob(
                                    $smsMessageResident->id,
                                    $residentData['phone'],
                                    $pattern->pattern_code,
                                    $variables
                                ));

                                // ذخیره نتیجه برای نمایش (pending چون async است)
                                $smsResult = $smsMessageResident;

                            } catch (\Exception $e) {
                                Log::error('Error queuing SMS in Units (Group)', [
                                    'report_id' => $reportId,
                                    'resident_id' => $residentDbId,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }

                    // خواندن رکورد از دیتابیس برای نمایش پاسخ
                    $submittedReport = \App\Models\ResidentReport::with(['report', 'report.category'])
                        ->find($residentReport->id);

                    if (!$submittedReport) {
                        throw new \Exception('رکورد از دیتابیس خوانده نشد!');
                    }

                    // ساخت آرایه sms_result برای نمایش (async - pending status)
                    $smsResultArray = null;
                    if ($smsResult) {
                        $smsResultArray = [
                            'status' => 'pending', // چون async است
                            'success' => null,
                            'message' => 'پیامک در صف ارسال قرار گرفت',
                            'response_code' => null,
                            'rec_id' => null,
                            'error_message' => null,
                            'api_response' => null,
                            'raw_response' => null,
                            'sent_at' => null,
                        ];
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
                        'sms_result' => $smsResultArray,
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
        $this->selectedCategoryId = null;
        $this->selectedReportId = null;
        $this->filteredReports = [];
        $this->notes = '';
        $this->description = '';
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
            ->where('resident_id', $resident->resident_id)
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
                ->where('resident_id', $resident->resident_id)
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
     * استخراج و جایگزینی متغیرها در الگو (سیستم جدید با pivot table)
     */
    protected function extractPatternVariables($patternText, $residentData, $residentDataFromDb = null, $report = null)
    {
        // پیدا کردن تمام متغیرها در الگو (مثل {0}, {1}, {2})
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[0])) {
            return []; // اگر متغیری وجود نداشت
        }

        $variableCodes = $matches[0]; // ['{0}', '{1}', '{2}']
        
        // استفاده از داده‌های دیتابیس اگر موجود باشد
        $resident = null;
        if ($residentDataFromDb) {
            $resident = (object) $residentDataFromDb;
        } else {
            // دریافت اطلاعات resident از دیتابیس
            if (isset($residentData['id'])) {
                $resident = \App\Models\Resident::where('resident_id', $residentData['id'])->first();
            }
        }

        if (!$resident) {
            Log::error('Units::extractPatternVariables - Resident not found', [
                'residentData' => $residentData,
                'residentDataFromDb' => $residentDataFromDb
            ]);
            return array_fill(0, count($variableCodes), ''); // آرایه خالی با تعداد کدها
        }

        // پیدا کردن الگوی مرتبط با متن
        $pattern = \App\Models\Pattern::where('text', $patternText)->first();
        if (!$pattern) {
            Log::error('Units::extractPatternVariables - Pattern not found', [
                'patternText' => $patternText
            ]);
            return array_fill(0, count($variableCodes), '');
        }

        // دریافت متغیر الگو
        $patternVariable = \App\Models\PatternVariable::where('pattern_code', $pattern->pattern_code)
            ->where('is_active', true)
            ->first();

        if (!$patternVariable) {
            Log::error('Units::extractPatternVariables - Pattern variable not found', [
                'pattern_code' => $pattern->pattern_code
            ]);
            return array_fill(0, count($variableCodes), '');
        }

        Log::info('Units::extractPatternVariables - Extracting variables', [
            'pattern_id' => $pattern->id,
            'pattern_code' => $pattern->pattern_code,
            'variable_codes' => $variableCodes,
            'table_name' => $patternVariable->table_name,
        ]);

        // ساخت آرایه متغیرها به ترتیب
        $variables = [];
        
        foreach ($variableCodes as $code) {
            // جستجو در جدول pivot
            $pivotData = \Illuminate\Support\Facades\DB::table('pattern_pattern_variables')
                ->where('pattern_id', $pattern->id)
                ->where('variable_code', $code)
                ->first();

            if ($pivotData && $pivotData->table_field) {
                $tableField = $pivotData->table_field;
                $tableName = $patternVariable->table_name;

                // استخراج مقدار
                $value = $this->getVariableValueFromTable($tableName, $tableField, $resident, $report);
                
                Log::info('Units::extractPatternVariables - Variable extracted', [
                    'code' => $code,
                    'table_field' => $tableField,
                    'table_name' => $tableName,
                    'value' => $value,
                ]);
                
                $variables[] = $value;
            } else {
                Log::warning('Units::extractPatternVariables - Pivot data not found', [
                    'code' => $code,
                    'pattern_id' => $pattern->id,
                ]);
                $variables[] = '';
            }
        }

        Log::info('Units::extractPatternVariables - Final variables', [
            'variables' => $variables,
            'variables_count' => count($variables),
        ]);

        return $variables;
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

    /**
     * دریافت پیام الگو با مقداردهی کدها برای گزارش انتخاب شده
     */
    public function getPatternMessageWithVariables()
    {
        $result = $this->calculatePatternMessage();
        $this->patternMessage = $result;
    }

    /**
     * محاسبه پیام الگو با مقداردهی کدها
     */
    private function calculatePatternMessage()
    {
        if (!$this->selectedReportId) {
            return [
                'success' => false,
                'message' => 'هیچ گزارشی انتخاب نشده است'
            ];
        }

        $report = Report::find($this->selectedReportId);
        if (!$report) {
            return [
                'success' => false,
                'message' => 'گزارش یافت نشد'
            ];
        }

        // دریافت اطلاعات resident فعلی
        $resident = null;
        if ($this->currentResident) {
            Log::info('Units::calculatePatternMessage - Looking for resident', [
                'current_resident_id' => $this->currentResident['id'],
                'current_resident_name' => $this->currentResident['name'] ?? 'N/A'
            ]);
            
            // استفاده از resident_id برای جستجو در جدول residents
            $resident = Resident::where('resident_id', $this->currentResident['id'])->first();
            
            Log::info('Units::calculatePatternMessage - Resident search result', [
                'resident_found' => $resident ? 'Yes' : 'No',
                'resident_db_id' => $resident ? $resident->id : 'N/A',
                'resident_name' => $resident ? $resident->resident_full_name : 'N/A'
            ]);
        }

        if (!$resident) {
            Log::error('Units::calculatePatternMessage - Resident not found', [
                'current_resident' => $this->currentResident,
                'search_id' => $this->currentResident['id'] ?? 'N/A'
            ]);
            
            return [
                'success' => false,
                'message' => 'اطلاعات اقامت‌گر یافت نشد'
            ];
        }

        // دریافت اولین الگوی فعال مرتبط با گزارش
        $pattern = $report->activePatterns()
            ->where('patterns.is_active', true)
            ->whereNotNull('patterns.pattern_code')
            ->orderBy('report_pattern.sort_order')
            ->first();

        if (!$pattern || !$pattern->pattern_code) {
            return [
                'success' => false,
                'message' => 'الگویی برای این گزارش تعریف نشده است'
            ];
        }

        // دریافت متغیر الگو
        $patternVariable = PatternVariable::where('pattern_code', $pattern->pattern_code)
            ->where('is_active', true)
            ->first();

        if (!$patternVariable) {
            return [
                'success' => false,
                'message' => 'متغیری برای این الگو تعریف نشده است'
            ];
        }

        // استخراج کدها از متن الگو
        preg_match_all('/\{(\d+)\}/', $pattern->text, $matches);
        $variableCodes = $matches[0];
        
        if (empty($variableCodes)) {
            return [
                'success' => true,
                'pattern_title' => $pattern->title,
                'original_message' => $pattern->text,
                'final_message' => $pattern->text,
                'variables' => []
            ];
        }

        // جایگزینی کدها با مقادیر
        $finalMessage = $pattern->text;
        $variables = [];

        foreach ($variableCodes as $code) {
            // جستجو در جدول pivot
            $pivotData = \Illuminate\Support\Facades\DB::table('pattern_pattern_variables')
                ->where('pattern_id', $pattern->id)
                ->where('variable_code', $code)
                ->first();

            if ($pivotData && $pivotData->table_field) {
                $tableField = $pivotData->table_field;
                $tableName = $patternVariable->table_name;

                // استخراج مقدار
                $value = $this->getVariableValueFromTable($tableName, $tableField, $resident, $report);
                
                $variables[] = [
                    'code' => $code,
                    'field' => $tableField,
                    'table' => $tableName,
                    'value' => $value
                ];

                // جایگزینی در متن
                $finalMessage = str_replace($code, $value, $finalMessage);
            } else {
                $variables[] = [
                    'code' => $code,
                    'field' => 'نامشخص',
                    'table' => 'نامشخص',
                    'value' => ''
                ];
            }
        }

        return [
            'success' => true,
            'pattern_title' => $pattern->title,
            'pattern_code' => $pattern->pattern_code,
            'original_message' => $pattern->text,
            'final_message' => $finalMessage,
            'variables' => $variables
        ];
    }

    /**
     * استخراج مقدار متغیر از جدول مشخص شده
     */
    private function getVariableValueFromTable($tableName, $tableField, $resident, $report)
    {
        if (empty($tableName) || empty($tableField)) {
            return '';
        }

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
            case 'room_name':
                return $resident->room_name ?? '';
            case 'bed_name':
                return $resident->bed_name ?? '';
            case 'unit_name':
                return $resident->unit_name ?? '';
            case 'contract_payment_date_jalali':
            case 'payment_date_jalali':
                return $resident->contract_payment_date_jalali ?? '';
            case 'contract_start_date_jalali':
                return $resident->contract_start_date_jalali ?? '';
            case 'contract_end_date_jalali':
                return $resident->contract_end_date_jalali ?? '';
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

    public function render()
    {
        $filteredUnits = $this->getFilteredUnits();

        return view('livewire.residents.units', [
            'filteredUnits' => $filteredUnits
        ]);
    }
}
