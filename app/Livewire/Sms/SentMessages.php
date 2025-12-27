<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;
use Livewire\WithPagination;

class SentMessages extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = ''; // all, sent, failed, pending
    public $selectedIds = [];
    public $selectAll = false;

    public function mount()
    {
        //
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

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->gotoPage(1);
    }

    public function resendSms($id)
    {
        $smsMessageResident = SmsMessageResident::with('smsMessage')->findOrFail($id);

        if (empty($smsMessageResident->phone)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'شماره تلفن موجود نیست.'
            ]);
            return;
        }

        try {
            $melipayamakService = new MelipayamakService();
            $from = config('services.melipayamak.from', '50002710051938');

            // ساخت متن پیام
            $messageText = $smsMessageResident->smsMessage->text;
            $messageText = str_replace('{resident_name}', $smsMessageResident->resident_name ?? '', $messageText);

            if ($smsMessageResident->smsMessage->link) {
                $messageText .= "\n" . $smsMessageResident->smsMessage->link;
            }

            // ارسال پیامک
            $result = $melipayamakService->sendSms($smsMessageResident->phone, $from, $messageText);
            
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
                    'text' => 'پیامک با موفقیت ارسال شد. ' . ($result['message'] ?? '')
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
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'ارسال مجدد با خطا مواجه شد: ' . $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $smsMessageResident->update([
                'status' => 'failed',
                'error_message' => 'خطا: ' . $e->getMessage(),
            ]);

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ارسال مجدد: ' . $e->getMessage()
            ]);
        }
    }

    public function resendMultipleSms()
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک پیام را انتخاب کنید.'
            ]);
            return;
        }

        $smsMessageResidents = SmsMessageResident::with('smsMessage')
            ->whereIn('id', $this->selectedIds)
            ->where('status', 'failed')
            ->get();

        if ($smsMessageResidents->isEmpty()) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'هیچ پیام ناموفقی برای ارسال مجدد انتخاب نشده است.'
            ]);
            return;
        }

        $melipayamakService = new MelipayamakService();
        $from = config('services.melipayamak.from', '50002710051938');
        $sentCount = 0;
        $failedCount = 0;

        foreach ($smsMessageResidents as $smsMessageResident) {
            if (empty($smsMessageResident->phone)) {
                $failedCount++;
                continue;
            }

            try {
                // ساخت متن پیام
                $messageText = $smsMessageResident->smsMessage->text;
                $messageText = str_replace('{resident_name}', $smsMessageResident->resident_name ?? '', $messageText);

                if ($smsMessageResident->smsMessage->link) {
                    $messageText .= "\n" . $smsMessageResident->smsMessage->link;
                }

                // ارسال پیامک
                $result = $melipayamakService->sendSms($smsMessageResident->phone, $from, $messageText);

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
            } catch (\Exception $e) {
                $smsMessageResident->update([
                    'status' => 'failed',
                    'error_message' => 'خطا: ' . $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $this->selectedIds = [];
        $this->selectAll = false;

        $this->dispatch('showAlert', [
            'type' => $failedCount > 0 ? 'warning' : 'success',
            'title' => $failedCount > 0 ? 'توجه!' : 'موفقیت!',
            'text' => "{$sentCount} پیامک با موفقیت ارسال شد." . ($failedCount > 0 ? " {$failedCount} پیامک با خطا مواجه شد." : '')
        ]);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = $this->sentMessagesQuery->pluck('id')->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds()
    {
        $this->selectAll = false;
    }

    public function getSentMessagesQueryProperty()
    {
        return SmsMessageResident::with('smsMessage')
            ->when($this->search, function ($query) {
                $query->where('resident_name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter && $this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function getStatusCountsProperty()
    {
        return [
            'all' => SmsMessageResident::count(),
            'sent' => SmsMessageResident::where('status', 'sent')->count(),
            'failed' => SmsMessageResident::where('status', 'failed')->count(),
            'pending' => SmsMessageResident::where('status', 'pending')->count(),
        ];
    }

    public function render()
    {
        $sentMessages = $this->sentMessagesQuery->paginate($this->perPage);
        
        // همگام‌سازی اطلاعات با API برای رکوردهای نمایش داده شده
        foreach ($sentMessages as $message) {
            if ($message->resident_id) {
                $apiService = new \App\Services\ResidentApiService();
                $apiService->syncResidentData($message);
            }
        }
        
        $statusCounts = $this->statusCounts;

        return view('livewire.sms.sent-messages', [
            'sentMessages' => $sentMessages,
            'statusCounts' => $statusCounts,
        ]);
    }
}
