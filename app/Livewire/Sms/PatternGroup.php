<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use App\Models\Report;
use App\Models\Resident;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
use App\Models\ResidentReport;
use App\Models\Settings;
use App\Services\MelipayamakService;
use App\Services\ResidentService;

class PatternGroup extends Component
{
    public $units = [];
    public $loading = true;
    public $error = null;
    public $search = '';
    public $expandedUnits = [];
    public $selectedResidents = [];
    
    // Form properties (بدون مودال)
    public $selectedPattern = null;
    public $selectedReport = null;
    public $patterns = [];
    public $reports = [];
    public $reportPatterns; // الگوهای مرتبط با گزارش انتخاب شده
    public $previewMessage = ''; // پیش‌نمایش پیام با متغیرهای جایگزین شده
    public $previewVariables = []; // متغیرهای استخراج شده برای پیش‌نمایش
    public $senderNumber = ''; // شماره فرستنده
    public $selectedSenderNumberId = null; // ID شماره فرستنده انتخاب شده
    public $availableSenderNumbers = []; // لیست شماره‌های فرستنده موجود
    public $patternReportWarning = null; // آلارم برای عدم وجود گزارش
    
    // Sending progress
    public $isSending = false;
    public $showProgressModal = false;
    public $isCancelled = false;
    public $sendingProgress = [
        'total' => 0,
        'sent' => 0,
        'failed' => 0,
        'current' => null,
        'current_index' => 0,
        'completed' => false,
        'result_message' => null,
    ];
    public $sendResults = []; // نتایج ارسال برای هر resident

    public function mount()
    {
        $this->reportPatterns = collect([]);
        $this->loadSenderNumbers();
        $this->loadUnits();
        $this->loadPatterns();
        $this->loadReports();
    }

    public function loadSenderNumbers()
    {
        $this->availableSenderNumbers = \App\Models\SenderNumber::getActivePatternNumbers();
        
        // اگر شماره‌ای انتخاب نشده، اولین شماره را به عنوان پیش‌فرض انتخاب کن
        if ($this->availableSenderNumbers->count() > 0 && !$this->selectedSenderNumberId) {
            $this->selectedSenderNumberId = $this->availableSenderNumbers->first()->id;
            $this->updateSenderNumber();
        } else {
            // اگر شماره‌ای در دیتابیس نیست، از config استفاده کن
            $this->senderNumber = config('services.melipayamak.pattern_from') 
                                ?? config('services.melipayamak.from') 
                                ?? 'تنظیم نشده';
        }
    }

    public function updatedSelectedSenderNumberId()
    {
        $this->updateSenderNumber();
    }

    public function updateSenderNumber()
    {
        if ($this->selectedSenderNumberId) {
            $senderNumber = \App\Models\SenderNumber::find($this->selectedSenderNumberId);
            if ($senderNumber) {
                $this->senderNumber = $senderNumber->number;
            }
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

    public function loadPatterns()
    {
        // فقط الگوهایی که تایید شده، فعال، دارای pattern_code و برایشان گزارش ست شده نمایش داده می‌شوند
        $this->patterns = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code')
            ->whereHas('reports', function ($query) {
                $query->where('report_pattern.is_active', true);
            })
            ->orderBy('title')
            ->get();
    }

    public function loadReports()
    {
        $this->reports = Report::with('category')->get();
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
        // اگر گزارش انتخاب شد، آلارم را پاک کن
        if ($value) {
            $this->patternReportWarning = null;
        }
        
        $this->loadReportPatterns();
        // اگر الگوهای مرتبط وجود داشت، اولین الگو را به صورت پیش‌فرض انتخاب کن
        if ($this->reportPatterns && $this->reportPatterns->count() > 0 && !$this->selectedPattern) {
            $this->selectedPattern = $this->reportPatterns->first()->id;
        }
        $this->updatePreview();
    }
    
    public function updatedSelectedPattern($value)
    {
        // بررسی اینکه آیا برای الگوی انتخابی گزارش ست شده یا نه
        $this->checkPatternReport();
        $this->updatePreview();
    }
    
    /**
     * بررسی اینکه آیا برای الگوی انتخابی گزارش ست شده یا نه
     */
    public function checkPatternReport()
    {
        $this->patternReportWarning = null;
        
        if (!$this->selectedPattern) {
            return;
        }
        
        $pattern = Pattern::find($this->selectedPattern);
        if (!$pattern) {
            return;
        }
        
        // اگر کاربر گزارش را انتخاب کرده باشد، نیازی به بررسی نیست
        if ($this->selectedReport) {
            return;
        }
        
        // بررسی اینکه آیا برای این الگو گزارش ست شده یا نه
        $reports = $pattern->reports()->wherePivot('is_active', true)->get();
        
        if ($reports->isEmpty()) {
            $this->patternReportWarning = 'گزارشی برای پیام انتخابی ثبت نشده';
        } else {
            // اگر گزارش ست شده باشد، اولین گزارش را به صورت خودکار انتخاب می‌کنیم
            $this->selectedReport = $reports->first()->id;
            $this->loadReportPatterns();
        }
    }

    /**
     * به‌روزرسانی پیش‌نمایش پیام با متغیرهای جایگزین شده
     */
    public function updatePreview()
    {
        $this->previewMessage = '';
        $this->previewVariables = [];
        
        if (!$this->selectedPattern || empty($this->selectedResidents)) {
            return;
        }
        
        $pattern = Pattern::find($this->selectedPattern);
        if (!$pattern || !$pattern->pattern_code) {
            return;
        }
        
        try {
            // استفاده از اولین resident برای پیش‌نمایش
            $firstResident = reset($this->selectedResidents);
            
            // استخراج متغیرها
            $variables = $this->extractPatternVariables($pattern->text, $firstResident);
            $this->previewVariables = $variables;
            
            // ساخت پیش‌نمایش پیام با جایگزینی متغیرها
            $previewText = $pattern->text;
            
            // جایگزینی متغیرها در متن
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
            \Log::error('Error updating preview in PatternGroup', [
                'error' => $e->getMessage(),
                'pattern_id' => $this->selectedPattern,
            ]);
            $this->previewMessage = '<span style="color: #dc3545;">خطا در ساخت پیش‌نمایش: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
    }

    public function toggleSelectResident($key, $resident, $bed, $unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];

        if (isset($this->selectedResidents[$key])) {
            unset($this->selectedResidents[$key]);
        } else {
            // پیدا کردن resident در جدول residents بر اساس resident_id از API
            $residentApiId = $resident['id'];
            $residentDb = Resident::where('resident_id', $residentApiId)->first();
            $residentDbId = $residentDb ? $residentDb->id : null;
            
            $this->selectedResidents[$key] = [
                'id' => $resident['id'],
                'db_id' => $residentDbId,
                'resident_id' => $resident['id'],
                'resident_name' => $resident['full_name'],
                'name' => $resident['full_name'],
                'phone' => $resident['phone'],
                'bed_id' => $bed['id'],
                'bed_name' => $bed['name'],
                'unit_id' => $unit['unit']['id'],
                'unit_name' => $unit['unit']['name'],
                'room_id' => $room['id'],
                'room_name' => $room['name']
            ];
        }
        
        $this->updatePreview();
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
                $residentApiId = $bed['resident']['id'];
                $residentDb = Resident::where('resident_id', $residentApiId)->first();
                $residentDbId = $residentDb ? $residentDb->id : null;
                
                if ($allSelected) {
                    unset($this->selectedResidents[$key]);
                } else {
                    $this->selectedResidents[$key] = [
                        'id' => $bed['resident']['id'],
                        'db_id' => $residentDbId,
                        'resident_id' => $bed['resident']['id'],
                        'resident_name' => $bed['resident']['full_name'],
                        'name' => $bed['resident']['full_name'],
                        'phone' => $bed['resident']['phone'],
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
        
        $this->updatePreview();
    }

    public function sendSms()
    {
        $this->validate([
            'selectedPattern' => 'required|exists:patterns,id',
        ], [
            'selectedPattern.required' => 'لطفاً یک الگو را انتخاب کنید.',
            'selectedPattern.exists' => 'الگوی انتخاب شده معتبر نیست.',
        ]);

        if (!$this->selectedPattern) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً یک الگو را انتخاب کنید.'
            ]);
            return;
        }

        // بررسی اینکه آیا برای الگوی انتخابی گزارش ست شده یا نه - باید قبل از هر کاری انجام شود
        $pattern = Pattern::find($this->selectedPattern);
        if ($pattern) {
            $reports = $pattern->reports()->wherePivot('is_active', true)->get();
            if ($reports->isEmpty() && !$this->selectedReport) {
                // اگر گزارش ست نشده باشد و کاربر هم گزارش انتخاب نکرده باشد، فقط آلارم نمایش می‌دهیم و هیچ کاری نمی‌کنیم
                $this->patternReportWarning = 'گزارشی برای پیام انتخابی ثبت نشده';
                $this->isSending = false;
                $this->showProgressModal = false;
                $this->dispatch('hide-progress-modal');
                return;
            }
        }

        if (empty($this->selectedResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک اقامت‌گر را انتخاب کنید.'
            ]);
            return;
        }

        $pattern = Pattern::find($this->selectedPattern);
        if (!$pattern || !$pattern->pattern_code) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'الگوی انتخاب شده معتبر نیست یا کد الگو ندارد.'
            ]);
            return;
        }

        // دریافت شماره فرستنده و API Key از شماره انتخاب شده
        $senderNumberObj = null;
        $apiKey = null;
        if ($this->selectedSenderNumberId) {
            $senderNumberObj = \App\Models\SenderNumber::find($this->selectedSenderNumberId);
            if ($senderNumberObj) {
                $apiKey = $senderNumberObj->api_key;
            }
        }

        // بررسی نهایی قبل از باز شدن مدال - اگر گزارش ست نشده باشد، ارسال نمی‌کنیم
        $pattern = Pattern::find($this->selectedPattern);
        if ($pattern) {
            $reports = $pattern->reports()->wherePivot('is_active', true)->get();
            // بررسی اینکه آیا گزارش انتخاب شده معتبر است یا نه
            $validReport = false;
            if ($this->selectedReport) {
                $selectedReportObj = Report::find($this->selectedReport);
                if ($selectedReportObj) {
                    // بررسی اینکه آیا این گزارش با الگو مرتبط است یا نه
                    $patternReports = $pattern->reports()->wherePivot('is_active', true)->pluck('reports.id')->toArray();
                    if (in_array($this->selectedReport, $patternReports)) {
                        $validReport = true;
                    }
                }
            }
            
            if ($reports->isEmpty() && !$validReport) {
                // اگر گزارش ست نشده باشد و گزارش انتخاب شده هم معتبر نباشد، فقط آلارم نمایش می‌دهیم و هیچ کاری نمی‌کنیم
                $this->patternReportWarning = 'گزارشی برای پیام انتخابی ثبت نشده';
                $this->isSending = false;
                $this->showProgressModal = false;
                $this->dispatch('hide-progress-modal');
                return;
            }
        }
        
        $melipayamakService = new MelipayamakService();
        
        // دریافت تنظیمات تاخیر از دیتابیس
        $settings = Settings::getSettings();
        $delayBeforeStart = ($settings->sms_delay_before_start ?? 2) * 1000000; // تبدیل ثانیه به میکروثانیه
        $delayBetweenMessages = ($settings->sms_delay_between_messages ?? 200) * 1000; // تبدیل میلی‌ثانیه به میکروثانیه
        
        // Reset progress و نمایش مدال - باید قبل از هر کار دیگری باشد
        $this->isSending = true;
        $this->isCancelled = false;
        $this->showProgressModal = true;
        $this->sendingProgress = [
            'total' => count($this->selectedResidents),
            'sent' => 0,
            'failed' => 0,
            'current' => 'در حال آماده‌سازی...',
            'current_index' => 0,
            'completed' => false,
            'result_message' => null,
        ];
        $this->sendResults = [];
        
        // Dispatch event برای نمایش مدال و قفل صفحه
        $this->dispatch('show-progress-modal');
        
        // اطمینان از render شدن مدال
        $this->dispatch('$refresh');
        
        // تاخیر قبل از شروع ارسال (از تنظیمات) - با به‌روزرسانی مدال
        if ($delayBeforeStart > 0) {
            $steps = 10; // 10 مرحله برای به‌روزرسانی مدال
            $stepDelay = $delayBeforeStart / $steps;
            for ($i = 0; $i < $steps; $i++) {
                if ($this->isCancelled) {
                    $this->isSending = false;
                    $this->showProgressModal = false;
                    $this->dispatch('hide-progress-modal');
                    return;
                }
                $remainingSeconds = ceil(($delayBeforeStart - ($i * $stepDelay)) / 1000000);
                $this->sendingProgress['current'] = 'در حال آماده‌سازی... (' . $remainingSeconds . ' ثانیه باقی مانده)';
                usleep($stepDelay);
            }
        }
        
        // به‌روزرسانی وضعیت - شروع واقعی ارسال
        $this->sendingProgress['current'] = 'شروع ارسال...';

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

        // ارسال به صورت تکی در حلقه با تاخیر قابل تنظیم
        $index = 0;
        foreach ($this->selectedResidents as $key => $residentData) {
            // بررسی لغو شدن
            if ($this->isCancelled) {
                break;
            }

            $index++;
            $this->sendingProgress['current_index'] = $index;
            
            if (empty($residentData['phone'])) {
                $this->sendingProgress['failed']++;
                $this->sendResults[] = [
                    'key' => $key,
                    'resident_name' => $residentData['name'] ?? $residentData['resident_name'] ?? 'بدون نام',
                    'phone' => $residentData['phone'] ?? 'بدون شماره',
                    'result' => [
                        'success' => false,
                        'message' => 'شماره تلفن موجود نیست',
                    ],
                ];
                // تاخیر بین پیام‌ها (از تنظیمات)
                if ($delayBetweenMessages > 0 && $index < count($this->selectedResidents)) {
                    usleep($delayBetweenMessages);
                }
                continue;
            }

            $this->sendingProgress['current'] = $residentData['name'] ?? $residentData['resident_name'] ?? 'بدون نام';

            try {
                // استخراج متغیرها از متن الگو برای هر resident
                $variables = $this->extractPatternVariables($pattern->text, $residentData, $reportData);

                // پیدا کردن resident در جدول residents
                $residentApiId = $residentData['id'] ?? $residentData['resident_id'];
                $residentDbId = $residentData['db_id'] ?? null;
                
                if (!$residentDbId) {
                    $resident = Resident::where('resident_id', $residentApiId)->first();
                    $residentDbId = $resident ? $resident->id : null;
                }

                // ثبت گزارش در جدول resident_reports
                $reportCreated = false;
                $reportError = null;
                $residentReportId = null;
                
                if ($this->selectedReport) {
                    try {
                        $residentReport = ResidentReport::create([
                            'report_id' => $this->selectedReport,
                            'resident_id' => $residentData['resident_id'], // استفاده از resident_id از API
                            'resident_name' => $residentData['name'] ?? $residentData['resident_name'],
                            'phone' => $residentData['phone'],
                            'unit_id' => $residentData['unit_id'] ?? null,
                            'unit_name' => $residentData['unit_name'] ?? null,
                            'room_id' => $residentData['room_id'] ?? null,
                            'room_name' => $residentData['room_name'] ?? null,
                            'bed_id' => $residentData['bed_id'] ?? null,
                            'bed_name' => $residentData['bed_name'] ?? null,
                        ]);
                        $reportCreated = true;
                        $residentReportId = $residentReport->id;
                        
                        \Log::info('Resident report created successfully', [
                            'resident_report_id' => $residentReportId,
                            'report_id' => $this->selectedReport,
                            'resident_id' => $residentDbId,
                            'resident_name' => $residentData['name'] ?? $residentData['resident_name'],
                        ]);
                    } catch (\Exception $e) {
                        $reportError = $e->getMessage();
                        \Log::error('Error creating resident report', [
                            'report_id' => $this->selectedReport,
                            'resident_id' => $residentDbId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }

                // اگر ResidentReport ایجاد شد، Event به صورت خودکار پیامک را ارسال می‌کند
                // پس نباید دستی پیامک ارسال کنیم
                if ($reportCreated) {
                    \Log::info('PatternGroup - ResidentReport created, SMS will be sent by Event', [
                        'resident_report_id' => $residentReportId,
                        'report_id' => $this->selectedReport,
                    ]);
                    
                    // تاخیر کوتاه برای اطمینان از اجرای Event
                    usleep(500000); // 0.5 ثانیه
                    
                    // بررسی وضعیت ارسال از Event
                    $smsMessageResident = SmsMessageResident::where('report_id', $this->selectedReport)
                        ->where('pattern_id', $pattern->id)
                        ->where('resident_id', $residentApiId)
                        ->where('created_at', '>=', now()->subMinutes(5)) // فقط در 5 دقیقه گذشته
                        ->first();
                    
                    if (!$smsMessageResident) {
                        // اگر Event هنوز رکورد را ایجاد نکرده، خودمان ایجاد می‌کنیم
                        $smsMessageResident = SmsMessageResident::create([
                            'sms_message_id' => null,
                            'report_id' => $this->selectedReport,
                            'pattern_id' => $pattern->id,
                            'is_pattern' => true,
                            'pattern_variables' => implode(';', $variables),
                            'resident_id' => $residentApiId, // استفاده از resident_id از API
                            'resident_name' => $residentData['name'] ?? $residentData['resident_name'],
                            'phone' => $residentData['phone'],
                            'title' => $pattern->title,
                            'description' => $pattern->text,
                            'status' => 'pending',
                        ]);
                    }
                    
                    // بررسی وضعیت ارسال
                    $smsMessageResident->refresh();
                    $isSuccess = $smsMessageResident->status === 'sent';
                    
                    $result = [
                        'success' => $isSuccess,
                        'message' => $isSuccess ? 'پیامک با موفقیت ارسال شد (از طریق Event)' : ($smsMessageResident->error_message ?? 'در حال ارسال...'),
                        'response_code' => $smsMessageResident->response_code ?? null,
                    ];
                } else {
                    // اگر ResidentReport ایجاد نشد، دستی پیامک ارسال می‌کنیم
                    \Log::info('PatternGroup - No ResidentReport created, sending SMS manually', [
                        'report_error' => $reportError,
                    ]);
                    
                // ایجاد رکورد در جدول sms_message_residents
                $smsMessageResident = SmsMessageResident::create([
                    'sms_message_id' => null,
                    'report_id' => $this->selectedReport,
                    'pattern_id' => $pattern->id,
                    'is_pattern' => true,
                    'pattern_variables' => implode(';', $variables),
                    'resident_id' => $residentApiId, // استفاده از resident_id از API
                    'resident_name' => $residentData['name'] ?? $residentData['resident_name'],
                    'phone' => $residentData['phone'],
                    'title' => $pattern->title,
                    'description' => $pattern->text,
                    'status' => 'pending',
                ]);

                // ارسال پیامک با الگو (استفاده از sendByBaseNumber2 که ابتدا Console API را امتحان می‌کند)
                $result = $melipayamakService->sendByBaseNumber2(
                    $residentData['phone'],
                    $pattern->pattern_code,
                    $variables,
                    $senderNumberObj ? $senderNumberObj->number : null,
                    $apiKey
                );

                if ($result['success']) {
                    $smsMessageResident->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'response_code' => $result['response_code'] ?? null,
                        'error_message' => null,
                    ]);
                } else {
                    $smsMessageResident->update([
                        'status' => 'failed',
                        'error_message' => $result['message'] ?? 'خطای نامشخص',
                        'response_code' => $result['response_code'] ?? null,
                        'api_response' => $result['api_response'] ?? null,
                        'raw_response' => $result['raw_response'] ?? null,
                    ]);
                    }
                }

                // ذخیره نتیجه
                $this->sendResults[] = [
                    'key' => $key,
                    'resident_name' => $residentData['name'] ?? $residentData['resident_name'],
                    'phone' => $residentData['phone'],
                    'result' => $result,
                    'report_created' => $reportCreated,
                    'report_error' => $reportError,
                    'resident_report_id' => $residentReportId,
                ];

                if ($result['success']) {
                    $this->sendingProgress['sent']++;
                } else {
                    $this->sendingProgress['failed']++;
                }
            } catch (\Exception $e) {
                \Log::error('Error sending pattern SMS to resident', [
                    'resident_id' => $residentData['id'] ?? null,
                    'phone' => $residentData['phone'] ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // بررسی اینکه آیا گزارش ثبت شده بود یا نه
                $reportCreated = isset($reportCreated) ? $reportCreated : false;
                $reportError = isset($reportError) ? $reportError : null;
                $residentReportId = isset($residentReportId) ? $residentReportId : null;
                
                $this->sendResults[] = [
                    'key' => $key,
                    'resident_name' => $residentData['name'] ?? $residentData['resident_name'],
                    'phone' => $residentData['phone'],
                    'result' => [
                        'success' => false,
                        'message' => 'خطا در ارسال: ' . $e->getMessage(),
                        'error' => $e->getMessage(),
                    ],
                    'report_created' => $reportCreated,
                    'report_error' => $reportError,
                    'resident_report_id' => $residentReportId,
                ];
                $this->sendingProgress['failed']++;
            }
            
            // تاخیر بین پیام‌ها (از تنظیمات) - فقط اگر آخرین پیام نباشد
            if (!$this->isCancelled && $index < count($this->selectedResidents) && $delayBetweenMessages > 0) {
                usleep($delayBetweenMessages);
            }
        }

        // اتمام ارسال - به‌روزرسانی نهایی و نمایش نتیجه
        $this->isSending = false;
        $this->sendingProgress['current'] = null;
        $this->sendingProgress['completed'] = true;
        
        // ساخت پیام نتیجه
        if (!$this->isCancelled) {
            $message = "{$this->sendingProgress['sent']} پیامک با موفقیت ارسال شد.";
            if ($this->sendingProgress['failed'] > 0) {
                $message .= " {$this->sendingProgress['failed']} پیامک با خطا مواجه شد.";
            }
            $this->sendingProgress['result_message'] = $message;
        } else {
            $this->sendingProgress['result_message'] = 'ارسال لغو شد. ' . $this->sendingProgress['sent'] . ' پیامک ارسال شده بود.';
        }
        
        // Reset لغو شدن
        $this->isCancelled = false;
    }

    /**
     * لغو ارسال پیام‌ها
     */
    public function cancelSending()
    {
        $this->isCancelled = true;
        $this->isSending = false;
        $this->showProgressModal = false;
        $this->sendingProgress['current'] = null;
        $this->dispatch('hide-progress-modal');
    }

    /**
     * بستن مدال پیشرفت
     */
    public function closeProgressModal()
    {
        $this->showProgressModal = false;
        $this->isSending = false;
        $this->isCancelled = false;
        $this->sendingProgress = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
            'current' => null,
            'current_index' => 0,
            'completed' => false,
            'result_message' => null,
        ];
        $this->dispatch('hide-progress-modal');
    }

    public function removeResident($key)
    {
        if (isset($this->selectedResidents[$key])) {
            unset($this->selectedResidents[$key]);
            $this->updatePreview();
        }
    }

    public function resendSms($resultIndex)
    {
        if (!isset($this->sendResults[$resultIndex])) {
            return;
        }

        $sendResult = $this->sendResults[$resultIndex];
        $result = $sendResult['result'];

        // اگر قبلاً موفق بوده، نیازی به ارسال مجدد نیست
        if ($result['success'] ?? false) {
            $this->dispatch('showAlert', [
                'type' => 'info',
                'title' => 'اطلاع',
                'text' => 'این پیامک قبلاً با موفقیت ارسال شده است.',
            ]);
            return;
        }

        // پیدا کردن resident در selectedResidents
        $residentKey = $sendResult['key'] ?? null;
        if (!$residentKey || !isset($this->selectedResidents[$residentKey])) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'اطلاعات اقامت‌گر یافت نشد.',
            ]);
            return;
        }

        $residentData = $this->selectedResidents[$residentKey];
        $pattern = Pattern::find($this->selectedPattern);
        
        if (!$pattern || !$pattern->pattern_code) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'الگوی انتخاب شده معتبر نیست.',
            ]);
            return;
        }

        // دریافت شماره فرستنده و API Key
        $senderNumberObj = null;
        $apiKey = null;
        if ($this->selectedSenderNumberId) {
            $senderNumberObj = \App\Models\SenderNumber::find($this->selectedSenderNumberId);
            if ($senderNumberObj) {
                $apiKey = $senderNumberObj->api_key;
            }
        }

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

        try {
            // استخراج متغیرها
            $variables = $this->extractPatternVariables($pattern->text, $residentData, $reportData);

            // پیدا کردن resident در جدول residents
            $residentApiId = $residentData['id'] ?? $residentData['resident_id'];
            $residentDbId = $residentData['db_id'] ?? null;
            
            if (!$residentDbId) {
                $resident = Resident::where('resident_id', $residentApiId)->first();
                $residentDbId = $resident ? $resident->id : null;
            }

            // پیدا کردن رکورد قبلی در sms_message_residents
            $smsMessageResident = SmsMessageResident::where('phone', $residentData['phone'])
                ->where('pattern_id', $pattern->id)
                ->where('report_id', $this->selectedReport)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$smsMessageResident) {
                // اگر رکوردی پیدا نشد، یک رکورد جدید ایجاد کن
                $smsMessageResident = SmsMessageResident::create([
                    'sms_message_id' => null,
                    'report_id' => $this->selectedReport,
                    'pattern_id' => $pattern->id,
                    'is_pattern' => true,
                    'pattern_variables' => implode(';', $variables),
                    'resident_id' => $residentApiId, // استفاده از resident_id از API
                    'resident_name' => $residentData['name'] ?? $residentData['resident_name'],
                    'phone' => $residentData['phone'],
                    'title' => $pattern->title,
                    'description' => $pattern->text,
                    'status' => 'pending',
                ]);
            } else {
                // به‌روزرسانی رکورد موجود
                $smsMessageResident->update([
                    'pattern_variables' => implode(';', $variables),
                    'status' => 'pending',
                ]);
            }

            $melipayamakService = new MelipayamakService();

            // ارسال مجدد (استفاده از sendByBaseNumber2 که ابتدا Console API را امتحان می‌کند)
            $newResult = $melipayamakService->sendByBaseNumber2(
                $residentData['phone'],
                $pattern->pattern_code,
                $variables,
                $senderNumberObj ? $senderNumberObj->number : null,
                $apiKey
            );

            // به‌روزرسانی نتیجه در sendResults
            $this->sendResults[$resultIndex]['result'] = $newResult;

            // به‌روزرسانی رکورد در دیتابیس
            if ($newResult['success']) {
                $smsMessageResident->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_code' => $newResult['response_code'] ?? null,
                    'error_message' => null,
                ]);
                
                // به‌روزرسانی آمار
                $this->sendingProgress['sent']++;
                $this->sendingProgress['failed']--;
                
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'پیامک با موفقیت ارسال شد.',
                ]);
            } else {
                $smsMessageResident->update([
                    'status' => 'failed',
                    'error_message' => $newResult['message'] ?? 'خطای نامشخص',
                    'response_code' => $newResult['response_code'] ?? null,
                    'api_response' => $newResult['api_response'] ?? null,
                    'raw_response' => $newResult['raw_response'] ?? null,
                ]);
                
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => $newResult['message'] ?? 'خطا در ارسال مجدد پیامک',
                ]);
            }

            // Force Livewire to update UI
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            \Log::error('Error resending SMS', [
                'resident_id' => $residentData['id'] ?? null,
                'phone' => $residentData['phone'] ?? null,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ارسال مجدد: ' . $e->getMessage(),
            ]);
        }
    }

    public function clearSelection()
    {
        $this->selectedResidents = [];
        $this->selectedReport = null;
        $this->selectedPattern = null;
        $this->reportPatterns = collect([]);
        $this->previewMessage = '';
        $this->previewVariables = [];
        $this->sendResults = [];
        $this->isSending = false;
        $this->sendingProgress = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
            'current' => null,
        ];
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
     */
    protected function extractPatternVariables($patternText, $resident, $reportData = null)
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
                $value = $this->getVariableValue($variable, $residentData, $reportData);
                $result[] = $value;
            } else {
                $result[] = '';
            }
        }

        return $result;
    }

    protected function getResidentData($resident)
    {
        try {
            $residentService = new ResidentService();
            $data = $residentService->getResidentById($resident['id'] ?? $resident['resident_id']);
            
            if ($data) {
                return $data;
            }
        } catch (\Exception $e) {
            \Log::error('Error getting resident data', [
                'resident_id' => $resident['id'] ?? $resident['resident_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'resident' => [
                'id' => $resident['id'] ?? $resident['resident_id'] ?? null,
                'full_name' => $resident['name'] ?? $resident['resident_name'] ?? '',
                'name' => $resident['name'] ?? $resident['resident_name'] ?? '',
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

    protected function getVariableValue($variable, $residentData, $reportData)
    {
        $field = $variable->table_field;
        $type = $variable->variable_type;

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
        } elseif ($type === 'report' && $reportData) {
            if (strpos($field, 'category.') === 0) {
                $key = substr($field, 9);
                return $reportData['category_' . $key] ?? '';
            } else {
                return $reportData[$field] ?? '';
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

        return view('livewire.sms.pattern-group', [
            'filteredUnits' => $filteredUnits
        ]);
    }
}
