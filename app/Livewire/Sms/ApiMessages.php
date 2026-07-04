<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use App\Services\MelipayamakService;
use Livewire\WithPagination;

class ApiMessages extends Component
{
    use WithPagination;

    public $location = 2; // 1 = دریافتی، 2 = ارسالی، -1 = همه
    public $perPage = 20;
    public $index = 0;
    public $from = null; // شماره فرستنده
    public $messages = [];
    public $loading = false;
    public $error = null;
    public $selectedRecIds = [];
    public $deliveryStatuses = [];
    public $rawResponse = null; // برای دیباگ

    public function mount()
    {
        $this->loadMessages();
    }

    public function loadMessages()
    {
        $this->loading = true;
        $this->error = null;
        $this->rawResponse = null;

        try {
            $melipayamakService = new MelipayamakService();
            $result = $melipayamakService->getMessages(
                location: $this->location,
                index: $this->index,
                count: $this->perPage,
                from: $this->from
            );

            // ذخیره پاسخ خام برای دیباگ
            $this->rawResponse = $result['raw_response'] ?? null;

            if ($result['success']) {
                $this->messages = $result['messages'];
                
                // نمایش لاگ برای دیباگ
                \Log::info('ApiMessages loaded', [
                    'messages_count' => count($this->messages),
                    'raw_response' => $result['raw_response'] ?? 'no raw response',
                ]);
                
                // دریافت وضعیت تحویل برای پیام‌های ارسالی
                if ($this->location == 2 && !empty($this->messages)) {
                    $this->loadDeliveryStatuses();
                }
            } else {
                $this->error = $result['message'];
                $this->messages = [];
                
                // نمایش پاسخ خام در خطا برای دیباگ
                if (isset($result['raw_response'])) {
                    $this->error .= ' | پاسخ: ' . substr($result['raw_response'], 0, 500);
                }
            }
        } catch (\Exception $e) {
            $this->error = 'خطا در بارگذاری پیام‌ها: ' . $e->getMessage();
            $this->messages = [];
        }

        $this->loading = false;
    }

    public function loadDeliveryStatuses()
    {
        $recIds = [];
        
        // استخراج RecId از پیام‌ها
        foreach ($this->messages as $message) {
            if (isset($message['RecId']) || isset($message['recId'])) {
                $recIds[] = $message['RecId'] ?? $message['recId'];
            }
        }

        if (!empty($recIds)) {
            try {
                $melipayamakService = new MelipayamakService();
                $result = $melipayamakService->getDeliveries($recIds);

                if ($result['success']) {
                    $this->deliveryStatuses = $result['deliveries'];
                }
            } catch (\Exception $e) {
                // اگر خطا در دریافت وضعیت بود، ادامه می‌دهیم
                \Log::error('Error loading delivery statuses', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function checkDeliveryStatus($recId)
    {
        try {
            $melipayamakService = new MelipayamakService();
            $result = $melipayamakService->getDeliveries($recId);

            if ($result['success']) {
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'وضعیت تحویل',
                    'text' => $result['message'],
                ]);
                
                // بارگذاری مجدد وضعیت‌ها
                $this->loadDeliveryStatuses();
            } else {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا',
                    'text' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا',
                'text' => 'خطا در دریافت وضعیت: ' . $e->getMessage(),
            ]);
        }
    }

    public function setLocation($location)
    {
        $this->location = $location;
        $this->index = 0;
        $this->messages = [];
        $this->deliveryStatuses = [];
        $this->loadMessages();
    }

    public function loadMore()
    {
        $this->index += $this->perPage;
        $this->loadMessages();
    }

    public function refresh()
    {
        $this->index = 0;
        $this->messages = [];
        $this->deliveryStatuses = [];
        $this->loadMessages();
    }

    public function getDeliveryStatusText($recId)
    {
        foreach ($this->deliveryStatuses as $delivery) {
            if (isset($delivery['recId']) && $delivery['recId'] == $recId) {
                return $delivery['text'] ?? $delivery['status'] ?? 'نامشخص';
            }
        }
        
        // اگر در آرایه نبود، از متد سرویس استفاده کن
        $melipayamakService = new MelipayamakService();
        return $melipayamakService->getDeliveryStatusText(0); // 0 = نامشخص
    }

    public function render()
    {
        return view('livewire.sms.api-messages');
    }
}
