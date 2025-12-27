<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\SmsMessage;
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
    public $reports = [];
    public $smsMessages = [];
    public $notes = '';

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

            // ارسال SMS
            $smsMessage = SmsMessage::find($this->selectedSmsMessage);
            $melipayamakService = new MelipayamakService();
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
                'resident_id' => $this->selectedResident['id'],
                'resident_name' => $this->selectedResident['name'],
                'phone' => $this->selectedResident['phone'],
                'title' => $smsMessage->title,
                'description' => $smsMessage->description,
                'status' => 'pending',
            ]);

            // ارسال پیامک
            $result = $melipayamakService->sendSms($this->selectedResident['phone'], $from, $messageText);

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