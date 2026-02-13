<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SmsMessageResident;
use App\Models\Setting;

class SmsDeliveryService
{
    private $username;
    private $password;
    private $apiUrl;

    public function __construct()
    {
        $settings = \App\Models\Settings::first();
        $this->username = $settings->sms_username ?? '';
        $this->password = $settings->sms_password ?? '';
        $this->apiUrl = 'http://api.payamak-panel.com/post/Send.asmx/GetDeliveries';
    }

    /**
     * دریافت وضعیت دلیوری پیامک‌ها
     */
    public function updateDeliveryStatus()
    {
        try {
            // دریافت پیام‌هایی که recId دارند و وضعیت آن‌ها sent است
            $messages = SmsMessageResident::whereNotNull('rec_id')
                ->where('status', 'sent')
                ->where('delivery_checked', false)
                ->limit(100) // محدودیت برای جلوگیری از درخواست‌های بزرگ
                ->get();

            if ($messages->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'هیچ پیامی برای بررسی وضعیت دلیوری وجود ندارد',
                    'updated_count' => 0
                ];
            }

            $recIds = $messages->pluck('rec_id')->toArray();
            
            // ارسال درخواست به وب سرویس
            $response = Http::asForm()->post($this->apiUrl, [
                'username' => $this->username,
                'password' => $this->password,
                'recIds' => implode(',', $recIds)
            ]);

            // ذخیره آخرین زمان بررسی
            \App\Models\Settings::updateOrCreate(
                ['key' => 'last_delivery_check'],
                ['value' => now()->format('Y-m-d H:i:s')]
            );

            if (!$response->successful()) {
                Log::error('SMS Delivery API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'خطا در ارتباط با وب سرویس پیامک',
                    'updated_count' => 0
                ];
            }

            // پردازش پاسخ
            $deliveryStatuses = $this->parseDeliveryResponse($response->body());
            $updatedCount = 0;

            foreach ($messages as $message) {
                if (isset($deliveryStatuses[$message->rec_id])) {
                    $status = $deliveryStatuses[$message->rec_id];
                    $newStatus = $this->mapDeliveryStatus($status);
                    
                    if ($newStatus !== $message->status) {
                        $message->update([
                            'status' => $newStatus,
                            'delivery_status' => $status,
                            'delivery_checked' => true,
                            'delivery_checked_at' => now()
                        ]);
                        $updatedCount++;
                    } else {
                        $message->update(['delivery_checked' => true]);
                    }
                }
            }

            return [
                'success' => true,
                'message' => "وضعیت {$updatedCount} پیامک به‌روزرسانی شد",
                'updated_count' => $updatedCount
            ];

        } catch (\Exception $e) {
            Log::error('Error updating SMS delivery status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'خطا در به‌روزرسانی وضعیت دلیوری: ' . $e->getMessage(),
                'updated_count' => 0
            ];
        }
    }

    /**
     * تجزیه پاسخ وب سرویس
     */
    private function parseDeliveryResponse($response)
    {
        $statuses = [];
        
        try {
            // پاسخ معمولاً به صورت آرایه‌ای از اعداد است
            $data = json_decode($response, true);
            
            if (is_array($data)) {
                foreach ($data as $index => $status) {
                    $statuses[$index] = $status;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error parsing delivery response', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);
        }

        return $statuses;
    }

    /**
     * تبدیل کد وضعیت دلیوری به وضعیت سیستم
     */
    private function mapDeliveryStatus($statusCode)
    {
        switch ($statusCode) {
            case 0: // ارسال شده به مخابرات
            case 8: // رسیده به مخابرات
            case 200: // ارسال شده
                return 'sent';
                
            case 1: // رسیده به گوشی
                return 'delivered';
                
            case 2: // نرسیده به گوشی
            case 3: // خطای مخابراتی
            case 5: // خطای نامشخص
            case 16: // نرسیده به مخابرات
            case 35: // لیست سیاه
                return 'failed';
                
            case -1: // ارسال نشده
            case -2: // ارسال بیش از 100 کد یکتا
            case -10: // بروز خطا در دریافت گزارش تحویل
            case 100: // نامشخص
            case 300: // فیلتر شده
            case 400: // در لیست ارسال
            case 500: // عدم پذیرش
                return 'failed';
                
            default:
                return 'sent'; // وضعیت پیش‌فرض
        }
    }

    /**
     * دریافت آمار وضعیت پیامک‌ها
     */
    public function getDeliveryStats()
    {
        return [
            'total' => SmsMessageResident::count(),
            'sent' => SmsMessageResident::where('status', 'sent')->count(),
            'delivered' => SmsMessageResident::where('status', 'delivered')->count(),
            'failed' => SmsMessageResident::where('status', 'failed')->count(),
            'pending' => SmsMessageResident::where('status', 'pending')->count(),
            'delivery_checked' => SmsMessageResident::where('delivery_checked', true)->count(),
            'delivery_pending' => SmsMessageResident::where('delivery_checked', false)
                ->whereNotNull('rec_id')
                ->where('status', 'sent')
                ->count()
        ];
    }
}
