<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MelipayamakService
{
    protected $username;
    protected $password;
    protected $baseUrl = 'https://rest.payamak-panel.com/api';

    public function __construct()
    {
        $this->username = config('services.melipayamak.username');
        $this->password = config('services.melipayamak.password');
    }

    /**
     * ارسال پیامک به یک شماره
     */
    public function sendSms($to, $from, $text)
    {
        try {
            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/SendSMS', [
                'username' => $this->username,
                'password' => $this->password,
                'to' => $to,
                'from' => $from,
                'text' => $text,
            ]);

            $result = $response->json();

            // بررسی پاسخ API - ممکن است به صورت XML یا JSON برگردد
            if (is_string($result)) {
                // اگر XML است، به JSON تبدیل می‌کنیم
                $xml = simplexml_load_string($result);
                if ($xml) {
                    $result = json_decode(json_encode($xml), true);
                }
            }

            // بررسی موفقیت آمیز بودن ارسال
            // معمولاً API ملی پیامک در صورت موفقیت یک عدد (RecId) برمی‌گرداند
            if ($response->successful()) {
                $recId = is_array($result) ? ($result['Value'] ?? $result['RetStatus'] ?? null) : $result;
                
                // اگر RecId عدد است، یعنی ارسال موفق بوده
                if (is_numeric($recId) && $recId > 0) {
                    return [
                        'success' => true,
                        'rec_id' => $recId,
                        'message' => 'پیامک با موفقیت ارسال شد',
                    ];
                }
                
                // بررسی خطا
                $errorMessage = is_array($result) ? ($result['StrRetStatus'] ?? 'خطا در ارسال پیامک') : 'خطا در ارسال پیامک';
                
                Log::error('Melipayamak SMS Error', [
                    'to' => $to,
                    'error' => $errorMessage,
                    'response' => $result,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                ];
            } else {
                Log::error('Melipayamak SMS HTTP Error', [
                    'to' => $to,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'خطا در اتصال به API: کد خطا ' . $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Melipayamak SMS Exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * ارسال پیامک به چند شماره
     */
    public function sendBulkSms($to, $from, $text)
    {
        try {
            // تبدیل آرایه به رشته با کاما
            if (is_array($to)) {
                $to = implode(',', $to);
            }

            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/SendSMS', [
                'username' => $this->username,
                'password' => $this->password,
                'to' => $to,
                'from' => $from,
                'text' => $text,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['StrRetStatus']) && $result['StrRetStatus'] === 'Ok') {
                return [
                    'success' => true,
                    'rec_id' => $result['RetStatus'] ?? null,
                    'message' => 'پیامک‌ها با موفقیت ارسال شدند',
                ];
            } else {
                $errorMessage = $result['StrRetStatus'] ?? 'خطا در ارسال پیامک';
                Log::error('Melipayamak Bulk SMS Error', [
                    'to' => $to,
                    'error' => $errorMessage,
                    'response' => $result,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Melipayamak Bulk SMS Exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * دریافت وضعیت ارسال پیامک
     */
    public function getDeliveryStatus($recId)
    {
        try {
            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/GetDelivery', [
                'username' => $this->username,
                'password' => $this->password,
                'recId' => $recId,
            ]);

            $result = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $result['RetStatus'] ?? null,
                    'message' => $result['StrRetStatus'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => 'خطا در دریافت وضعیت',
            ];
        } catch (\Exception $e) {
            Log::error('Melipayamak Delivery Status Exception', [
                'rec_id' => $recId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس',
            ];
        }
    }

    /**
     * دریافت موجودی حساب
     */
    public function getCredit()
    {
        try {
            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/GetCredit', [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['RetStatus'])) {
                return [
                    'success' => true,
                    'credit' => $result['RetStatus'],
                ];
            }

            return [
                'success' => false,
                'credit' => 0,
            ];
        } catch (\Exception $e) {
            Log::error('Melipayamak Get Credit Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'credit' => 0,
            ];
        }
    }
}
