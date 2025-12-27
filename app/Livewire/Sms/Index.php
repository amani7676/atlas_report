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
        // بارگذاری مجدد residents برای اطمینان از دریافت داده‌های به‌روز
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
                                $resident = $bed['resident'];
                                
                                // لاگ برای دیباگ - فقط برای اولین رکورد
                                if (empty($this->residents)) {
                                    \Log::info('API Resident Data Sample', [
                                        'resident' => $resident,
                                        'unit' => $unit['unit'] ?? null,
                                        'room' => $room,
                                        'bed' => $bed
                                    ]);
                                }
                                
                                // دریافت همه فیلدهای ممکن از API
                                $residentData = [
                                    'id' => $resident['id'] ?? null,
                                    'name' => $resident['full_name'] ?? $resident['name'] ?? '',
                                    'phone' => $resident['phone'] ?? '',
                                    'unit_name' => $unit['unit']['name'] ?? '',
                                    'unit_code' => $unit['unit']['code'] ?? $unit['unit']['id'] ?? '',
                                    'room_name' => $room['name'] ?? '',
                                    'bed_name' => $bed['name'] ?? '',
                                    'national_id' => $resident['national_id'] ?? $resident['national_code'] ?? '',
                                ];
                                
                                // دریافت تاریخ‌ها با بررسی نام‌های مختلف
                                $contractStartDate = $resident['contract_start_date'] 
                                    ?? $resident['start_date'] 
                                    ?? $resident['contract_start']
                                    ?? $resident['contract']['start_date'] ?? null;
                                    
                                $contractEndDate = $resident['contract_end_date'] 
                                    ?? $resident['end_date'] 
                                    ?? $resident['contract_end']
                                    ?? $resident['contract']['end_date'] ?? null;
                                    
                                $contractExpiryDate = $resident['contract_expiry_date'] 
                                    ?? $resident['expiry_date'] 
                                    ?? $resident['contract_expiry']
                                    ?? $resident['contract']['expiry_date'] ?? null;
                                
                                $residentData['contract_start_date'] = $contractStartDate;
                                $residentData['contract_end_date'] = $contractEndDate;
                                $residentData['contract_expiry_date'] = $contractExpiryDate;
                                
                                $this->residents[] = $residentData;
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

    /**
     * جایگزینی متغیرها در متن پیام با اطلاعات واقعی کاربر
     */
    protected function replaceVariables($text, $resident)
    {
        // لاگ داده‌های دریافتی
        \Log::debug('replaceVariables called', [
            'resident_keys' => array_keys($resident),
            'resident_data' => $resident,
            'original_text' => $text
        ]);

        $replacements = [
            '{resident_name}' => $resident['name'] ?? '',
            '{resident_phone}' => $resident['phone'] ?? '',
            '{unit_name}' => $resident['unit_name'] ?? '',
            '{unit_code}' => $resident['unit_code'] ?? '',
            '{room_name}' => $resident['room_name'] ?? '',
            '{room_number}' => preg_replace('/[^0-9]/', '', $resident['room_name'] ?? ''),
            '{bed_name}' => $resident['bed_name'] ?? '',
            '{national_id}' => $resident['national_id'] ?? '',
        ];

        // تبدیل تاریخ‌ها به فرمت شمسی
        $contractStartDate = $resident['contract_start_date'] ?? null;
        if ($contractStartDate) {
            $replacements['{contract_start_date}'] = $this->formatJalaliDate($contractStartDate);
        } else {
            $replacements['{contract_start_date}'] = '';
        }

        $contractEndDate = $resident['contract_end_date'] ?? null;
        if ($contractEndDate) {
            $replacements['{contract_end_date}'] = $this->formatJalaliDate($contractEndDate);
        } else {
            $replacements['{contract_end_date}'] = '';
        }

        $contractExpiryDate = $resident['contract_expiry_date'] ?? null;
        if ($contractExpiryDate) {
            $replacements['{contract_expiry_date}'] = $this->formatJalaliDate($contractExpiryDate);
        } else {
            $replacements['{contract_expiry_date}'] = '';
        }

        // تاریخ امروز
        $replacements['{today}'] = $this->formatJalaliDate(now()->toDateString());

        // لاگ replacements
        \Log::debug('Replacements', [
            'replacements' => $replacements
        ]);

        $result = $text;
        foreach ($replacements as $key => $value) {
            $oldResult = $result;
            $result = str_replace($key, $value, $result);
            if ($oldResult !== $result) {
                \Log::debug('Replaced variable', [
                    'key' => $key,
                    'value' => $value,
                    'before' => $oldResult,
                    'after' => $result
                ]);
            }
        }

        \Log::debug('Final result', [
            'final_text' => $result
        ]);

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
            $carbonDate = null;
            
            // اگر تاریخ به صورت string است، آن را به Carbon تبدیل می‌کنیم
            if (is_string($date)) {
                $carbonDate = \Carbon\Carbon::parse($date);
            } elseif ($date instanceof \Carbon\Carbon) {
                $carbonDate = $date;
            } else {
                return (string)$date;
            }

            // استفاده از کتابخانه Morilog/Jalali برای تبدیل به شمسی
            if (class_exists(\Morilog\Jalali\Jalalian::class)) {
                return \Morilog\Jalali\Jalalian::fromCarbon($carbonDate)->format('Y/m/d');
            }

            // اگر کتابخانه موجود نبود، تاریخ میلادی را برمی‌گردانیم
            return $carbonDate->format('Y/m/d');
        } catch (\Exception $e) {
            \Log::error('Error formatting date', [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return is_string($date) ? $date : (string)$date;
        }
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

        // بارگذاری مجدد residents برای اطمینان از دریافت داده‌های به‌روز
        $this->loadResidents();

        $smsMessage = $this->selectedMessage;
        $melipayamakService = new MelipayamakService();
        $from = config('services.melipayamak.from', '5000...');
        $sentCount = 0;
        $failedCount = 0;

        // متن پایه پیام
        $baseMessageText = $smsMessage->text;

        foreach ($this->selectedResidents as $residentId) {
            $resident = collect($this->residents)->firstWhere('id', $residentId);
            
            if (!$resident || empty($resident['phone'])) {
                $failedCount++;
                continue;
            }

            // لاگ برای دیباگ
            \Log::info('Replacing variables for resident', [
                'resident_id' => $residentId,
                'resident_data' => $resident,
                'original_text' => $baseMessageText
            ]);

            // جایگزینی متغیرها با اطلاعات واقعی کاربر
            $personalizedText = $this->replaceVariables($baseMessageText, $resident);
            
            // لاگ متن نهایی
            \Log::info('Final personalized text', [
                'resident_id' => $residentId,
                'final_text' => $personalizedText
            ]);
            
            // اضافه کردن لینک در صورت وجود
            if ($smsMessage->link) {
                $personalizedText .= "\n" . $smsMessage->link;
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
            $result = $melipayamakService->sendSms($resident['phone'], $from, $personalizedText);

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