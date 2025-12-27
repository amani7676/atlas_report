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

        // ساخت متن پیام با لینک در صورت وجود
        $messageText = $smsMessage->text;
        if ($smsMessage->link) {
            $messageText .= "\n" . $smsMessage->link;
        }

        foreach ($this->selectedResidents as $residentData) {
            if (empty($residentData['phone'])) {
                $failedCount++;
                continue;
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

            // ساخت متن پیام شخصی‌سازی شده
            $personalizedText = str_replace('{resident_name}', $residentData['resident_name'], $messageText);

            // ارسال پیامک
            $result = $melipayamakService->sendSms($residentData['phone'], $from, $personalizedText);

            if ($result['success']) {
                $smsMessageResident->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $sentCount++;
            } else {
                $smsMessageResident->update([
                    'status' => 'failed',
                    'error_message' => $result['message'],
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