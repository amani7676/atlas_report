<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Resident;
use App\Models\SmsMessage;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;
use App\Services\ResidentService;
use App\Jobs\SyncResidentsFromApi;

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
    public $reports = [];
    public $smsMessages = [];
    public $notes = '';
    public $syncing = false;
    public $syncMessage = '';
    public $showApiResponseModal = false; // نمایش مودال پاسخ API
    public $apiResponseData = null; // داده‌های پاسخ API

    public function mount()
    {
        $this->loadUnits();
        $this->loadReports();
        $this->loadSmsMessages();
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
    public function syncResidents($showToast = true)
    {
        $this->syncing = true;
        $this->syncMessage = 'در حال همگام‌سازی...';
        
        try {
            // اجرای Job همگام‌سازی
            $job = new SyncResidentsFromApi();
            $job->handle();
            
            // دریافت آمار همگام‌سازی
            $lastSync = \Illuminate\Support\Facades\Cache::get('residents_last_sync');
            
            // بررسی تعداد واقعی در دیتابیس
            $totalInDb = \App\Models\Resident::count();
            $lastSyncedResident = \App\Models\Resident::orderBy('last_synced_at', 'desc')->first();
            $lastSyncTime = $lastSyncedResident && $lastSyncedResident->last_synced_at 
                ? $lastSyncedResident->last_synced_at->format('Y-m-d H:i:s') 
                : 'نامشخص';
            
            // بارگذاری مجدد داده‌ها
            $this->loadUnits();
            
            // نمایش آلارم فقط اگر showToast = true باشد (برای همگام‌سازی دستی)
            if ($showToast) {
                // نمایش آلارم ساده
                $this->dispatch('showToast', [
                    'type' => 'success',
                    'title' => 'Success',
                    'message' => '',
                    'duration' => 3000,
                ]);
            }
            
            // پاک کردن پیام همگام‌سازی از صفحه
            $this->syncMessage = '';
        } catch (\Exception $e) {
            \Log::error('Error syncing residents from Manual component', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // نمایش آلارم خطا فقط اگر showToast = true باشد
            if ($showToast) {
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => '',
                    'duration' => 3000,
                ]);
            }
            
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

    public function loadSmsMessages()
    {
        $this->smsMessages = SmsMessage::where('is_active', true)->get();
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
        $this->selectedSmsMessage = null;
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

        // بررسی انتخاب پیام
        if (!$this->selectedSmsMessage) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً یک پیام را انتخاب کنید.'
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
            // این گزارش همیشه ثبت می‌شود (چه پیامک ارسال شود چه نشود)
            // برای گزارش‌های اطلاع‌رسانی (notification) و تخلف (violation) هر دو ثبت می‌شود
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

            // استفاده از residentDbId که قبلاً پیدا شده
            // ایجاد رکورد در جدول sms_message_residents
            $smsMessageResident = SmsMessageResident::create([
                'sms_message_id' => $smsMessage->id,
                'report_id' => $this->selectedReport,
                'is_pattern' => false,
                'resident_id' => $residentDbId, // استفاده از id جدول residents
                'resident_name' => $this->selectedResident['name'],
                'phone' => $this->selectedResident['phone'],
                'title' => $smsMessage->title,
                'description' => $smsMessage->description,
                'status' => 'pending',
            ]);

            // ارسال پیامک عادی
            $result = $melipayamakService->sendSms($this->selectedResident['phone'], $from, $messageText);

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
                    'selected_sms_message' => $this->selectedSmsMessage,
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

            // ذخیره داده‌های پاسخ API برای نمایش در مودال
            $this->apiResponseData = [
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'پیام نامشخص',
                'response_code' => $result['response_code'] ?? null,
                'rec_id' => $result['rec_id'] ?? null,
                'raw_response' => $result['raw_response'] ?? null,
                'api_response' => $result['api_response'] ?? null,
                'http_status_code' => $result['http_status_code'] ?? null,
                'is_pattern' => false,
                'phone' => $this->selectedResident['phone'] ?? null,
                'resident_name' => $this->selectedResident['name'] ?? null,
            ];

            // بررسی موفقیت ارسال
            $isSuccess = isset($result['success']) && $result['success'] === true;
            
            \Log::info('Checking SMS result success', [
                'is_success' => $isSuccess,
                'result_success' => $result['success'] ?? 'not set',
                'result_message' => $result['message'] ?? 'no message',
            ]);
            
            if ($isSuccess) {
                $smsMessageResident->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_code' => $result['response_code'] ?? null,
                    'error_message' => null,
                ]);
                
                // ساخت HTML برای نمایش پاسخ سرور در آلارم
                $responseHtml = '<div style="text-align: right; direction: rtl;">';
                $responseHtml .= '<p><strong>گزارش ثبت شد و پیامک با موفقیت ارسال شد.</strong></p>';
                $responseHtml .= '<div style="margin-top: 15px; padding: 12px; background: #f0f9ff; border-radius: 5px; border-right: 3px solid #28a745;">';
                $responseHtml .= '<strong style="color: #28a745; display: block; margin-bottom: 8px;">✓ پاسخ سرور:</strong>';
                $responseHtml .= '<div style="font-size: 13px; line-height: 1.8;">';
                $responseHtml .= '<span style="color: #28a745;">✓ پیام: ' . htmlspecialchars($result['message'] ?? 'ارسال موفق') . '</span><br>';
                if (isset($result['rec_id'])) {
                    $responseHtml .= '<span style="color: #666;">RecId: <strong style="font-family: monospace;">' . htmlspecialchars($result['rec_id']) . '</strong></span><br>';
                }
                if (isset($result['response_code'])) {
                    $responseHtml .= '<span style="color: #666;">کد پاسخ: <strong style="font-family: monospace;">' . htmlspecialchars($result['response_code']) . '</strong></span><br>';
                }
                if (isset($result['http_status_code'])) {
                    $responseHtml .= '<span style="color: #666;">کد HTTP: <strong>' . htmlspecialchars($result['http_status_code']) . '</strong></span><br>';
                }
                if (isset($result['raw_response'])) {
                    $responseHtml .= '<div style="margin-top: 8px; padding: 8px; background: white; border-radius: 3px; border: 1px solid #dee2e6;">';
                    $responseHtml .= '<strong style="color: #666; font-size: 11px;">پاسخ خام:</strong><br>';
                    $responseHtml .= '<code style="font-size: 11px; color: #333; word-break: break-all;">' . htmlspecialchars(substr($result['raw_response'], 0, 200)) . (strlen($result['raw_response']) > 200 ? '...' : '') . '</code>';
                    $responseHtml .= '</div>';
                }
                $responseHtml .= '</div>';
                $responseHtml .= '</div>';
                $responseHtml .= '</div>';
                
                \Log::info('Dispatching success alert with HTML', [
                    'html_length' => strlen($responseHtml),
                    'html_preview' => substr($responseHtml, 0, 200),
                ]);
                
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'گزارش ثبت شد و پیامک با موفقیت ارسال شد.',
                    'html' => $responseHtml,
                ]);
            } else {
                $smsMessageResident->update([
                    'status' => 'failed',
                    'error_message' => $result['message'],
                    'response_code' => $result['response_code'] ?? null,
                    'api_response' => $result['api_response'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                ]);
                
                // ساخت HTML برای نمایش خطا و پاسخ سرور در آلارم
                $errorHtml = '<div style="text-align: right; direction: rtl;">';
                $errorHtml .= '<p><strong>گزارش ثبت شد اما ارسال پیامک با خطا مواجه شد.</strong></p>';
                $errorHtml .= '<div style="margin-top: 15px; padding: 12px; background: #fff3cd; border-radius: 5px; border-right: 3px solid #dc3545;">';
                $errorHtml .= '<strong style="color: #dc3545; display: block; margin-bottom: 8px;">✗ پاسخ سرور:</strong>';
                $errorHtml .= '<div style="font-size: 13px; line-height: 1.8;">';
                $errorHtml .= '<span style="color: #dc3545;">✗ پیام خطا: <strong>' . htmlspecialchars($result['message'] ?? 'خطای نامشخص') . '</strong></span><br>';
                if (isset($result['response_code'])) {
                    $errorHtml .= '<span style="color: #666;">کد پاسخ: <strong style="font-family: monospace;">' . htmlspecialchars($result['response_code']) . '</strong></span><br>';
                }
                if (isset($result['http_status_code'])) {
                    $errorHtml .= '<span style="color: #666;">کد HTTP: <strong>' . htmlspecialchars($result['http_status_code']) . '</strong></span><br>';
                }
                if (isset($result['raw_response'])) {
                    $errorHtml .= '<div style="margin-top: 8px; padding: 8px; background: white; border-radius: 3px; border: 1px solid #dee2e6;">';
                    $errorHtml .= '<strong style="color: #666; font-size: 11px;">پاسخ خام:</strong><br>';
                    $errorHtml .= '<code style="font-size: 11px; color: #333; word-break: break-all;">' . htmlspecialchars(substr($result['raw_response'], 0, 200)) . (strlen($result['raw_response']) > 200 ? '...' : '') . '</code>';
                    $errorHtml .= '</div>';
                }
                $errorHtml .= '</div>';
                $errorHtml .= '</div>';
                $errorHtml .= '</div>';
                
                \Log::info('Dispatching error alert with HTML', [
                    'html_length' => strlen($errorHtml),
                    'html_preview' => substr($errorHtml, 0, 200),
                ]);
                
                $this->dispatch('showAlert', [
                    'type' => 'warning',
                    'title' => 'توجه!',
                    'text' => 'گزارش ثبت شد اما ارسال پیامک با خطا مواجه شد.',
                    'html' => $errorHtml,
                ]);
            }

            // نمایش مودال پاسخ API
            $this->showApiResponseModal = true;
            $this->closeModal();
        } catch (\Exception $e) {
            \Log::error('Error in Manual SMS submit', [
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
        $this->selectedSmsMessage = null;
        $this->notes = '';
    }

    public function closeApiResponseModal()
    {
        $this->showApiResponseModal = false;
        $this->apiResponseData = null;
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