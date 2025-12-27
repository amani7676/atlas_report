<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use App\Models\SmsMessage;
use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;
use App\Jobs\SendSmsJob;
use Illuminate\Support\Facades\Http;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Modal properties
    public $showModal = false;
    public $modalMode = 'create'; // create or edit
    public $editingId = null;
    
    // Form properties
    public $title = '';
    public $description = '';
    public $link = '';
    public $text = '';
    public $message_type = 'manual';
    public $is_active = true;
    
    // Send SMS properties
    public $showSendModal = false;
    public $selectedMessage = null;
    public $selectedResidents = [];
    public $residents = [];
    public $residentSearch = '';
    public $sendingMode = 'manual'; // manual, group, automatic
    public $loadingResidents = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'link' => 'nullable|url',
        'text' => 'required|string|max:1000',
        'message_type' => 'required|in:manual,group,automatic',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        //
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $smsMessage = SmsMessage::findOrFail($id);
        $this->editingId = $id;
        $this->title = $smsMessage->title;
        $this->description = $smsMessage->description;
        $this->link = $smsMessage->link;
        $this->text = $smsMessage->text;
        $this->message_type = $smsMessage->message_type;
        $this->is_active = $smsMessage->is_active;
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function openSendModal($id)
    {
        $this->selectedMessage = SmsMessage::findOrFail($id);
        $this->loadResidents();
        $this->selectedResidents = [];
        $this->residentSearch = '';
        $this->showSendModal = true;
    }

    public function loadResidents()
    {
        $this->loadingResidents = true;
        try {
            $response = Http::timeout(30)->get('http://atlas2.test/api/residents');
            
            if ($response->successful()) {
                $units = $response->json();
                $this->residents = [];
                
                foreach ($units as $unit) {
                    foreach ($unit['rooms'] ?? [] as $room) {
                        foreach ($room['beds'] ?? [] as $bed) {
                            if (isset($bed['resident']) && $bed['resident']) {
                                $this->residents[] = [
                                    'id' => $bed['resident']['id'],
                                    'name' => $bed['resident']['full_name'] ?? '',
                                    'phone' => $bed['resident']['phone'] ?? '',
                                    'unit_name' => $unit['unit']['name'] ?? '',
                                    'room_name' => $room['name'] ?? '',
                                ];
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در دریافت لیست اقامت‌گران: ' . $e->getMessage()
            ]);
        }
        $this->loadingResidents = false;
    }

    public function getFilteredResidents()
    {
        if (empty($this->residentSearch)) {
            return $this->residents;
        }
        
        $search = strtolower($this->residentSearch);
        return array_filter($this->residents, function ($resident) use ($search) {
            return strpos(strtolower($resident['name']), $search) !== false ||
                   strpos(strtolower($resident['phone']), $search) !== false ||
                   strpos(strtolower($resident['unit_name']), $search) !== false;
        });
    }

    public function toggleResidentSelection($residentId)
    {
        $key = array_search($residentId, $this->selectedResidents);
        if ($key !== false) {
            unset($this->selectedResidents[$key]);
            $this->selectedResidents = array_values($this->selectedResidents);
        } else {
            $this->selectedResidents[] = $residentId;
        }
    }

    public function selectAllResidents()
    {
        $filtered = $this->getFilteredResidents();
        $filteredIds = array_column($filtered, 'id');
        
        if (count(array_intersect($filteredIds, $this->selectedResidents)) === count($filteredIds)) {
            // همه انتخاب شده‌اند، همه را برمی‌گردانیم
            $this->selectedResidents = array_diff($this->selectedResidents, $filteredIds);
        } else {
            // همه را انتخاب می‌کنیم
            $this->selectedResidents = array_unique(array_merge($this->selectedResidents, $filteredIds));
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'link' => $this->link,
            'text' => $this->text,
            'message_type' => $this->message_type,
            'is_active' => $this->is_active,
        ];

        if ($this->modalMode === 'edit' && $this->editingId) {
            $smsMessage = SmsMessage::findOrFail($this->editingId);
            $smsMessage->update($data);
            
            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'پیام با موفقیت به‌روزرسانی شد.'
            ]);
        } else {
            SmsMessage::create($data);
            
            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'پیام جدید با موفقیت ایجاد شد.'
            ]);
        }

        $this->closeModal();
    }

    public function sendSms()
    {
        if (empty($this->selectedResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک اقامت‌گر را انتخاب کنید.'
            ]);
            return;
        }

        $smsMessage = $this->selectedMessage;
        $melipayamakService = new MelipayamakService();
        $from = config('services.melipayamak.from', '5000...');
        $sentCount = 0;
        $failedCount = 0;

        // ساخت متن پیام با لینک در صورت وجود
        $messageText = $smsMessage->text;
        if ($smsMessage->link) {
            $messageText .= "\n" . $smsMessage->link;
        }

        foreach ($this->selectedResidents as $residentId) {
            $resident = collect($this->residents)->firstWhere('id', $residentId);
            
            if (!$resident || empty($resident['phone'])) {
                $failedCount++;
                continue;
            }

            // ایجاد رکورد در جدول sms_message_residents
            $smsMessageResident = SmsMessageResident::create([
                'sms_message_id' => $smsMessage->id,
                'resident_id' => $resident['id'],
                'resident_name' => $resident['name'],
                'phone' => $resident['phone'],
                'title' => $smsMessage->title,
                'description' => $smsMessage->description,
                'status' => 'pending',
            ]);

            // ارسال پیامک
            $result = $melipayamakService->sendSms($resident['phone'], $from, $messageText);

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
    }

    public function delete($id)
    {
        $smsMessage = SmsMessage::findOrFail($id);
        $smsMessage->delete();

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'پیام با موفقیت حذف شد.'
        ]);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeSendModal()
    {
        $this->showSendModal = false;
        $this->selectedMessage = null;
        $this->selectedResidents = [];
        $this->residentSearch = '';
    }

    private function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->link = '';
        $this->text = '';
        $this->message_type = 'manual';
        $this->is_active = true;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getSmsMessagesQueryProperty()
    {
        return SmsMessage::when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('text', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $smsMessages = $this->smsMessagesQuery->paginate($this->perPage);
        $filteredResidents = $this->getFilteredResidents();

        return view('livewire.sms.index', [
            'smsMessages' => $smsMessages,
            'filteredResidents' => $filteredResidents,
        ]);
    }
}