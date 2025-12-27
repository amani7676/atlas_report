<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\SmsMessage;
use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;

class Group extends Component
{
    public $units = [];
    public $loading = true;
    public $error = null;
    public $search = '';
    public $expandedUnits = [];
    public $selectedResidents = [];
    
    // Modal properties
    public $showSendModal = false;
    public $selectedSmsMessage = null;
    public $smsMessages = [];

    public function mount()
    {
        $this->loadUnits();
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

    public function loadSmsMessages()
    {
        $this->smsMessages = SmsMessage::where('is_active', true)->get();
    }

    /**
     * جایگزینی متغیرها در متن پیام با اطلاعات واقعی کاربر
     */
    protected function replaceVariables($text, $resident)
    {
        $replacements = [
            '{resident_name}' => $resident['resident_name'] ?? '',
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

        $this->selectedSmsMessage = null;
        $this->showSendModal = true;
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
            'selectedSmsMessage' => 'required|exists:sms_messages,id',
        ], [
            'selectedSmsMessage.required' => 'لطفاً یک پیام را انتخاب کنید.',
            'selectedSmsMessage.exists' => 'پیام انتخاب شده معتبر نیست.',
        ]);

        if (!$this->selectedSmsMessage) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً یک پیام را انتخاب کنید.'
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

        $smsMessage = SmsMessage::find($this->selectedSmsMessage);
        $melipayamakService = new MelipayamakService();
        $from = config('services.melipayamak.from', '5000...');
        $sentCount = 0;
        $failedCount = 0;

        // متن پایه پیام
        $baseMessageText = $smsMessage->text;

        foreach ($this->selectedResidents as $residentData) {
            if (empty($residentData['phone'])) {
                $failedCount++;
                continue;
            }

            // جایگزینی متغیرها با اطلاعات واقعی کاربر
            $personalizedText = $this->replaceVariables($baseMessageText, $residentData);
            
            // اضافه کردن لینک در صورت وجود
            if ($smsMessage->link) {
                $personalizedText .= "\n" . $smsMessage->link;
            }

            // ایجاد رکورد در جدول sms_message_residents
            $smsMessageResident = SmsMessageResident::create([
                'sms_message_id' => $smsMessage->id,
                'resident_id' => $residentData['resident_id'],
                'resident_name' => $residentData['resident_name'],
                'phone' => $residentData['phone'],
                'title' => $smsMessage->title,
                'description' => $smsMessage->description,
                'status' => 'pending',
            ]);

            // ارسال پیامک
            $result = $melipayamakService->sendSms($residentData['phone'], $from, $personalizedText);

            // ارسال پاسخ به console.log
            $this->dispatch('logMelipayamakResponse', $result);

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
        }

        $this->dispatch('showAlert', [
            'type' => $failedCount > 0 ? 'warning' : 'success',
            'title' => $failedCount > 0 ? 'توجه!' : 'موفقیت!',
            'text' => "{$sentCount} پیامک با موفقیت ارسال شد." . ($failedCount > 0 ? " {$failedCount} پیامک با خطا مواجه شد." : '')
        ]);

        $this->closeSendModal();
        $this->selectedResidents = [];
    }

    public function closeSendModal()
    {
        $this->showSendModal = false;
        $this->selectedSmsMessage = null;
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

    public function render()
    {
        $filteredUnits = $this->getFilteredUnits();

        return view('livewire.sms.group', [
            'filteredUnits' => $filteredUnits
        ]);
    }
}