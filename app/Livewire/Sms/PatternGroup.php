<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\Report;
use App\Models\Resident;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
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
    
    // Modal properties
    public $showSendModal = false;
    public $selectedPattern = null;
    public $selectedReport = null;
    public $patterns = [];
    public $reports = [];

    public function mount()
    {
        $this->loadUnits();
        $this->loadPatterns();
        $this->loadReports();
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
        $this->patterns = Pattern::where('is_active', true)
            ->whereNotNull('pattern_code')
            ->orderBy('title')
            ->get();
    }

    public function loadReports()
    {
        $this->reports = Report::with('category')->get();
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

    public function openSendModal()
    {
        if (empty($this->selectedResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک اقامت‌گر را انتخاب کنید.'
            ]);
            return;
        }

        $this->selectedPattern = null;
        $this->selectedReport = null;
        $this->showSendModal = true;
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
                'id' => $resident['id'], // resident_id از API
                'db_id' => $residentDbId, // id از جدول residents
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
                // پیدا کردن resident در جدول residents بر اساس resident_id از API
                $residentApiId = $bed['resident']['id'];
                $residentDb = Resident::where('resident_id', $residentApiId)->first();
                $residentDbId = $residentDb ? $residentDb->id : null;
                
                if ($allSelected) {
                    unset($this->selectedResidents[$key]);
                } else {
                    $this->selectedResidents[$key] = [
                        'id' => $bed['resident']['id'], // resident_id از API
                        'db_id' => $residentDbId, // id از جدول residents
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

        $melipayamakService = new MelipayamakService();
        $sentCount = 0;
        $failedCount = 0;
        $this->sendResults = [];

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

        foreach ($this->selectedResidents as $residentData) {
            if (empty($residentData['phone'])) {
                $failedCount++;
                continue;
            }

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

                // ایجاد رکورد در جدول sms_message_residents
                $smsMessageResident = SmsMessageResident::create([
                    'report_id' => $this->selectedReport,
                    'pattern_id' => $pattern->id,
                    'is_pattern' => true,
                    'pattern_variables' => implode(';', $variables),
                    'resident_id' => $residentDbId,
                    'resident_name' => $residentData['resident_name'] ?? $residentData['name'],
                    'phone' => $residentData['phone'],
                    'title' => $pattern->title,
                    'description' => $pattern->text,
                    'status' => 'pending',
                ]);

                // ارسال پیامک با الگو
                $result = $melipayamakService->sendByBaseNumber2(
                    $residentData['phone'],
                    $pattern->pattern_code,
                    $variables
                );

                // ارسال پاسخ به console.log
                $this->dispatch('logMelipayamakResponse', $result);
                
                // ذخیره نتیجه برای نمایش در پاپاپ
                $this->sendResults[] = [
                    'resident_name' => $residentData['resident_name'] ?? $residentData['name'],
                    'phone' => $residentData['phone'],
                    'result' => $result
                ];

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
                        'error_message' => $result['message'],
                        'response_code' => $result['response_code'] ?? null,
                        'api_response' => $result['api_response'] ?? null,
                        'raw_response' => $result['raw_response'] ?? null,
                    ]);
                    $failedCount++;
                }
            } catch (\Exception $e) {
                \Log::error('Error sending pattern SMS to resident', [
                    'resident_id' => $residentData['id'] ?? null,
                    'phone' => $residentData['phone'] ?? null,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        // ساخت HTML برای نمایش نتایج و پاسخ‌های سرور
        $responseHtml = '<div style="text-align: right; direction: rtl;">';
        $responseHtml .= '<p><strong>' . ($failedCount > 0 ? 'توجه!' : 'موفقیت!') . '</strong></p>';
        $responseHtml .= '<p>' . $sentCount . ' پیامک با موفقیت ارسال شد.' . ($failedCount > 0 ? ' ' . $failedCount . ' پیامک با خطا مواجه شد.' : '') . '</p>';
        
        // نمایش جزئیات پاسخ‌های سرور
        if (!empty($this->sendResults)) {
            $responseHtml .= '<div style="margin-top: 15px; max-height: 300px; overflow-y: auto;">';
            $responseHtml .= '<strong>جزئیات پاسخ‌های سرور:</strong>';
            foreach ($this->sendResults as $index => $sendResult) {
                $result = $sendResult['result'];
                $responseHtml .= '<div style="margin-top: 10px; padding: 8px; background: ' . ($result['success'] ? '#f0f9ff' : '#fff3cd') . '; border-radius: 5px; border-right: 3px solid ' . ($result['success'] ? '#28a745' : '#f72585') . ';">';
                $responseHtml .= '<strong>' . ($index + 1) . '. ' . htmlspecialchars($sendResult['resident_name']) . ' (' . htmlspecialchars($sendResult['phone']) . ')</strong><br>';
                $responseHtml .= '<span style="color: ' . ($result['success'] ? '#28a745' : '#f72585') . ';">';
                $responseHtml .= ($result['success'] ? '✓ ' : '✗ ') . htmlspecialchars($result['message'] ?? 'بدون پیام');
                $responseHtml .= '</span><br>';
                if (isset($result['response_code'])) {
                    $responseHtml .= '<span style="color: #666; font-size: 11px;">کد: ' . htmlspecialchars($result['response_code']) . '</span><br>';
                }
                if (isset($result['rec_id'])) {
                    $responseHtml .= '<span style="color: #666; font-size: 11px;">RecId: ' . htmlspecialchars($result['rec_id']) . '</span><br>';
                }
                if (isset($result['raw_response']) && !$result['success']) {
                    $responseHtml .= '<span style="color: #666; font-size: 10px; margin-top: 3px; display: block;">پاسخ خام: ' . htmlspecialchars($result['raw_response']) . '</span>';
                }
                $responseHtml .= '</div>';
            }
            $responseHtml .= '</div>';
        }
        
        $responseHtml .= '</div>';
        
        $this->dispatch('showAlert', [
            'type' => $failedCount > 0 ? 'warning' : 'success',
            'title' => $failedCount > 0 ? 'توجه!' : 'موفقیت!',
            'text' => "{$sentCount} پیامک با موفقیت ارسال شد." . ($failedCount > 0 ? " {$failedCount} پیامک با خطا مواجه شد." : ''),
            'html' => $responseHtml
        ]);

        $this->closeSendModal();
        $this->selectedResidents = [];
    }

    public function closeSendModal()
    {
        $this->showSendModal = false;
        $this->selectedPattern = null;
        $this->selectedReport = null;
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
    protected function extractPatternVariables($patternText, $resident, $reportData = null)
    {
        // پیدا کردن تمام متغیرها در الگو (مثل {0}, {1}, {2})
        preg_match_all('/\{(\d+)\}/', $patternText, $matches);
        
        if (empty($matches[1])) {
            return []; // اگر متغیری وجود نداشت
        }

        // دریافت اطلاعات کامل resident از دیتابیس
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
            $residentService = new ResidentService();
            $data = $residentService->getResidentById($resident['id'] ?? $resident['resident_id']); // resident_id از API
            
            if ($data) {
                return $data;
            }
        } catch (\Exception $e) {
            \Log::error('Error getting resident data', [
                'resident_id' => $resident['id'] ?? $resident['resident_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        // در صورت خطا، از داده‌های موجود استفاده می‌کنیم
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

    /**
     * دریافت مقدار متغیر بر اساس فیلد جدول
     * این متد مقدار متغیر را از داده‌های resident یا report استخراج می‌کند
     */
    protected function getVariableValue($variable, $residentData, $reportData)
    {
        $field = $variable->table_field;
        $type = $variable->variable_type;

        if ($type === 'user') {
            // فیلدهای کاربر
            if (strpos($field, 'unit_') === 0) {
                $key = substr($field, 5); // حذف 'unit_' از ابتدا
                $value = $residentData['unit'][$key] ?? '';
                return $value;
            } elseif (strpos($field, 'room_') === 0) {
                $key = substr($field, 5); // حذف 'room_' از ابتدا
                $value = $residentData['room'][$key] ?? '';
                return $value;
            } elseif (strpos($field, 'bed_') === 0) {
                $key = substr($field, 4); // حذف 'bed_' از ابتدا
                $value = $residentData['bed'][$key] ?? '';
                return $value;
            } else {
                // فیلدهای مستقیم resident (مثل full_name, phone, name, national_id, etc.)
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
                
                return $value;
            }
        } elseif ($type === 'report' && $reportData) {
            // فیلدهای گزارش
            if (strpos($field, 'category.') === 0) {
                $key = substr($field, 9); // حذف 'category.' از ابتدا
                $value = $reportData['category_' . $key] ?? '';
                return $value;
            } else {
                $value = $reportData[$field] ?? '';
                return $value;
            }
        } elseif ($type === 'general') {
            // فیلدهای عمومی
            if ($field === 'today') {
                $value = $this->formatJalaliDate(now()->toDateString());
                return $value;
            }
        }

        return '';
    }

    public function render()
    {
        $filteredUnits = $this->getFilteredUnits();

        return view('livewire.sms.pattern-group', [
            'filteredUnits' => $filteredUnits
        ]);
    }
}
