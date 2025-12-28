<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\SmsMessage;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;

class Manual extends Component
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
    public $selectedSmsMessage = null;
    public $selectedPattern = null;
    public $usePattern = false; // آیا از الگو استفاده می‌شود یا پیامک عادی
    public $reports = [];
    public $smsMessages = [];
    public $patterns = [];
    public $notes = '';

    public function mount()
    {
        $this->loadUnits();
        $this->loadReports();
        $this->loadSmsMessages();
        $this->loadPatterns();
    }
    
    public function loadPatterns()
    {
        $this->patterns = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code')
            ->orderBy('title')
            ->get();
    }

    public function loadUnits()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $response = Http::timeout(30)->get('http://atlas2.test/api/residents');

            if ($response->successful()) {
                $this->units = $response->json();
                $this->sortData();
            } else {
                $this->error = 'خطا در دریافت اطلاعات از API';
                $this->units = [];
            }
        } catch (\Exception $e) {
            $this->error = 'خطا در اتصال به API: ' . $e->getMessage();
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

    public function loadReports()
    {
        $this->reports = Report::with('category')->get();
    }

    public function loadSmsMessages()
    {
        $this->smsMessages = SmsMessage::where('is_active', true)->get();
    }

    public function openModal($resident, $bed, $unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];

        $this->selectedResident = [
            'id' => $resident['id'],
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
        $this->selectedSmsMessage = null;
        $this->selectedPattern = null;
        $this->usePattern = false;
        $this->notes = '';
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

        // بررسی اینکه آیا از الگو استفاده می‌شود یا پیامک عادی
        if ($this->usePattern) {
            if (!$this->selectedPattern) {
                $this->dispatch('showAlert', [
                    'type' => 'warning',
                    'title' => 'هشدار!',
                    'text' => 'لطفاً یک الگو را انتخاب کنید.'
                ]);
                return;
            }
        } else {
            if (!$this->selectedSmsMessage) {
                $this->dispatch('showAlert', [
                    'type' => 'warning',
                    'title' => 'هشدار!',
                    'text' => 'لطفاً یک پیام را انتخاب کنید.'
                ]);
                return;
            }
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
            // ثبت گزارش
            $residentReport = ResidentReport::create([
                'report_id' => $this->selectedReport,
                'resident_id' => $this->selectedResident['id'],
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

            $melipayamakService = new MelipayamakService();
            $result = null;
            $smsMessageResident = null;

            if ($this->usePattern && $this->selectedPattern) {
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
                $variables = $this->extractPatternVariables($pattern->text, $this->selectedResident);
                
                // ایجاد رکورد در جدول sms_message_residents
                $smsMessageResident = SmsMessageResident::create([
                    'pattern_id' => $pattern->id,
                    'is_pattern' => true,
                    'pattern_variables' => implode(';', $variables),
                    'resident_id' => $this->selectedResident['id'],
                    'resident_name' => $this->selectedResident['name'],
                    'phone' => $this->selectedResident['phone'],
                    'title' => $pattern->title,
                    'description' => $pattern->text,
                    'status' => 'pending',
                ]);

                // ارسال پیامک با الگو
                $result = $melipayamakService->sendByBaseNumber2(
                    $this->selectedResident['phone'],
                    $pattern->pattern_code,
                    $variables
                );
            } else {
                // ارسال پیامک عادی (بدون الگو)
                $smsMessage = SmsMessage::find($this->selectedSmsMessage);
                $from = config('services.melipayamak.from', '5000...');
                
                // ساخت متن پیام با جایگزینی متغیرها
                $messageText = $this->replaceVariables($smsMessage->text, $this->selectedResident);
                
                $report = Report::find($this->selectedReport);
                if ($report) {
                    $violationInfo = "\n\nگزارش: " . $report->title;
                    if ($report->description) {
                        $violationInfo .= "\n" . $report->description;
                    }
                    $messageText = str_replace('{violation}', $violationInfo, $messageText);
                }
                
                if ($smsMessage->link) {
                    $messageText .= "\n" . $smsMessage->link;
                }

                // ایجاد رکورد در جدول sms_message_residents
                $smsMessageResident = SmsMessageResident::create([
                    'sms_message_id' => $smsMessage->id,
                    'is_pattern' => false,
                    'resident_id' => $this->selectedResident['id'],
                    'resident_name' => $this->selectedResident['name'],
                    'phone' => $this->selectedResident['phone'],
                    'title' => $smsMessage->title,
                    'description' => $smsMessage->description,
                    'status' => 'pending',
                ]);

                // ارسال پیامک عادی
                $result = $melipayamakService->sendSms($this->selectedResident['phone'], $from, $messageText);
            }

            // ارسال پاسخ به console.log
            $this->dispatch('logMelipayamakResponse', $result);

            if ($result['success']) {
                $smsMessageResident->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_code' => $result['response_code'] ?? null,
                    'error_message' => null,
                ]);
                
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'گزارش ثبت شد و پیامک با موفقیت ارسال شد. ' . ($result['message'] ?? '')
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
                    'text' => 'گزارش ثبت شد اما ارسال پیامک با خطا مواجه شد: ' . $result['message']
                ]);
            }

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ثبت گزارش و ارسال پیامک: ' . $e->getMessage()
            ]);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedResident = null;
        $this->selectedReport = null;
        $this->selectedSmsMessage = null;
        $this->selectedPattern = null;
        $this->usePattern = false;
        $this->notes = '';
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
     * جایگزینی متغیرها در متن پیام با اطلاعات واقعی کاربر
     */
    protected function replaceVariables($text, $resident)
    {
        $replacements = [
            '{resident_name}' => $resident['name'] ?? '',
            '{resident_phone}' => $resident['phone'] ?? '',
            '{unit_name}' => $resident['unit_name'] ?? '',
            '{room_name}' => $resident['room_name'] ?? '',
            '{room_number}' => preg_replace('/[^0-9]/', '', $resident['room_name'] ?? ''),
            '{bed_name}' => $resident['bed_name'] ?? '',
        ];

        // تاریخ امروز
        $replacements['{today}'] = $this->formatJalaliDate(now()->toDateString());

        $result = $text;
        foreach ($replacements as $key => $value) {
            $result = str_replace($key, $value, $result);
        }

        return $result;
    }

    /**
     * استخراج و جایگزینی متغیرها در الگو
     * متغیرها به ترتیب {0}, {1}, {2} و ... استخراج می‌شوند
     */
    protected function extractPatternVariables($patternText, $resident)
    {
        // پیدا کردن تمام متغیرها در الگو (مثل {0}, {1}, {2})
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[1])) {
            return []; // اگر متغیری وجود نداشت
        }

        // دریافت اطلاعات کامل resident از API
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
                ];
            }
        }

        // بارگذاری متغیرها از دیتابیس
        $variables = PatternVariable::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('code'); // کلید بر اساس کد (مثل {0}, {1})

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
                // اگر متغیر در دیتابیس پیدا نشد، مقدار خالی
                $result[] = '';
            }
        }

        return $result;
    }

    /**
     * دریافت اطلاعات کامل resident از API
     */
    protected function getResidentData($resident)
    {
        try {
            $response = Http::timeout(10)->get('http://atlas2.test/api/residents');
            if ($response->successful()) {
                $units = $response->json();
                foreach ($units as $unit) {
                    foreach ($unit['rooms'] ?? [] as $room) {
                        foreach ($room['beds'] ?? [] as $bed) {
                            if (isset($bed['resident']) && $bed['resident']['id'] == $resident['id']) {
                                return [
                                    'resident' => $bed['resident'],
                                    'unit' => $unit['unit'] ?? null,
                                    'room' => $room,
                                    'bed' => $bed,
                                ];
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // در صورت خطا، از داده‌های موجود استفاده می‌کنیم
        }

        return [
            'resident' => $resident,
            'unit' => ['name' => $resident['unit_name'] ?? ''],
            'room' => ['name' => $resident['room_name'] ?? ''],
            'bed' => ['name' => $resident['bed_name'] ?? ''],
        ];
    }

    /**
     * دریافت مقدار متغیر بر اساس فیلد جدول
     */
    protected function getVariableValue($variable, $residentData, $reportData)
    {
        $field = $variable->table_field;
        $type = $variable->variable_type;

        if ($type === 'user') {
            // فیلدهای کاربر
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
                return $residentData['resident'][$field] ?? '';
            }
        } elseif ($type === 'report' && $reportData) {
            // فیلدهای گزارش
            if (strpos($field, 'category.') === 0) {
                $key = substr($field, 9);
                return $reportData['category_' . $key] ?? '';
            } else {
                return $reportData[$field] ?? '';
            }
        } elseif ($type === 'general') {
            // فیلدهای عمومی
            if ($field === 'today') {
                return $this->formatJalaliDate(now()->toDateString());
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

    public function render()
    {
        $filteredUnits = $this->getFilteredUnits();

        return view('livewire.sms.manual', [
            'filteredUnits' => $filteredUnits
        ]);
    }
}