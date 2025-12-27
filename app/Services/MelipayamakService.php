<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MelipayamakService
{
    protected $username;
    protected $password; // APIKey
    protected $baseUrl = 'https://rest.payamak-panel.com/api';

    public function __construct()
    {
        $this->username = config('services.melipayamak.username');
        // استفاده از APIKey به جای password
        $this->password = config('services.melipayamak.api_key') ?: config('services.melipayamak.password');
    }

    /**
     * ارسال پیامک به یک شماره
     */
    public function sendSms($to, $from, $text)
    {
        try {
            $data = [
                'username' => $this->username,
                'password' => $this->password, // APIKey
                'to' => $to,
                'from' => $from,
                'text' => $text,
            ];

            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/SendSMS', $data);

            // پاسخ API به صورت string عددی است (RecId در صورت موفقیت یا کد خطا)
            $responseBody = trim($response->body());
            $responseCode = (string)$responseBody;

            // بررسی موفقیت آمیز بودن ارسال
            // اگر پاسخ یک عدد مثبت باشد (و کد خطا نباشد)، یعنی RecId است و ارسال موفق بوده
            // کدهای خطا: 0, 2, 3, 4, 5, 6, 7, 9, 10, 11, 12, 14, 15, 16, 17, 18, 35, 108, 109, 110
            $errorCodes = ['0', '2', '3', '4', '5', '6', '7', '9', '10', '11', '12', '14', '15', '16', '17', '18', '35', '108', '109', '110'];
            
            if ($response->successful() && is_numeric($responseBody)) {
                // اگر کد خطا نباشد، یعنی RecId است
                if (!in_array($responseCode, $errorCodes) && (int)$responseBody > 0) {
                    return [
                        'success' => true,
                        'rec_id' => (int)$responseBody,
                        'response_code' => $responseCode,
                        'message' => 'پیامک با موفقیت ارسال شد (RecId: ' . $responseBody . ')',
                    ];
                }
            }

            // در صورت خطا
            $errorMessage = $this->getErrorMessage($responseCode);
            
            Log::error('Melipayamak SMS Error', [
                'to' => $to,
                'from' => $from,
                'error' => $errorMessage,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
                'http_status_code' => $response->status(),
            ]);

            return [
                'success' => false,
                'response_code' => $responseCode,
                'message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('Melipayamak SMS Exception', [
                'to' => $to,
                'from' => $from,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * دریافت پیام خطا بر اساس کد پاسخ
     * بر اساس مستندات رسمی ملی پیامک
     */
    protected function getErrorMessage($responseCode)
    {
        $errorMessages = [
            '0' => 'نام کاربری یا رمز عبور اشتباه است',
            '2' => 'اعتبار کافی نمی باشد',
            '3' => 'محدودیت در ارسال روزانه',
            '4' => 'محدودیت در حجم ارسال',
            '5' => 'شماره فرستنده معتبر نمی باشد',
            '6' => 'سامانه در حال بروزرسانی می باشد',
            '7' => 'متن حاوی کلمه فیلتر شده می باشد',
            '9' => 'ارسال از خطوط عمومی از طریق وب سرویس امکان پذیر نمی باشد',
            '10' => 'کاربر مورد نظر فعال نمی باشد',
            '11' => 'ارسال نشده',
            '12' => 'مدارک کاربر کامل نمی باشد',
            '14' => 'متن حاوی لینک می باشد',
            '15' => 'ارسال به بیش از 1 شماره همراه بدون درج "لغو11" ممکن نیست',
            '16' => 'شماره گیرنده‌ای یافت نشد',
            '17' => 'متن پیامک خالی می باشد',
            '18' => 'شماره گیرنده نامعتبر است',
            '35' => 'شماره در لیست سیاه مخالفات می‌باشد',
            '108' => 'مسدود شدن IP به دلیل تلاش ناموفق استفاده از API',
            '109' => 'الزام تنظیم IP مجاز برای استفاده از API',
            '110' => 'الزام استفاده از ApiKey به جای رمز عبور',
        ];

        $code = (string)$responseCode;
        $message = $errorMessages[$code] ?? null;
        
        if ($message) {
            return "کد خطا {$code}: {$message}";
        }
        
        return "خطا در ارسال پیامک (کد خطا: {$responseCode})";
    }

    /**
     * ارسال پیامک به چند شماره
     * توجه: برای ارسال به چند شماره، شماره‌ها را با کاما جدا کنید
     */
    public function sendBulkSms($to, $from, $text)
    {
        try {
            // تبدیل آرایه به رشته با کاما
            if (is_array($to)) {
                $to = implode(',', $to);
            }

            $data = [
                'username' => $this->username,
                'password' => $this->password, // APIKey
                'to' => $to,
                'from' => $from,
                'text' => $text,
            ];

            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/SendSMS', $data);

            $responseBody = trim($response->body());
            $responseCode = (string)$responseBody;
            $errorCodes = ['0', '2', '3', '4', '5', '6', '7', '9', '10', '11', '12', '14', '15', '16', '17', '18', '35', '108', '109', '110'];

            if ($response->successful() && is_numeric($responseBody)) {
                if (!in_array($responseCode, $errorCodes) && (int)$responseBody > 0) {
                    return [
                        'success' => true,
                        'rec_id' => (int)$responseBody,
                        'response_code' => $responseCode,
                        'message' => 'پیامک‌ها با موفقیت ارسال شدند (RecId: ' . $responseBody . ')',
                    ];
                }
            }

            $errorMessage = $this->getErrorMessage($responseCode);
            
            Log::error('Melipayamak Bulk SMS Error', [
                'to' => $to,
                'from' => $from,
                'error' => $errorMessage,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
            ]);

            return [
                'success' => false,
                'response_code' => $responseCode,
                'message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('Melipayamak Bulk SMS Exception', [
                'to' => $to,
                'from' => $from,
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
            $data = [
                'username' => $this->username,
                'password' => $this->password, // APIKey
                'recId' => $recId,
            ];

            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/GetDelivery', $data);
            $responseBody = trim($response->body());

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $responseBody,
                    'message' => 'وضعیت دریافت شد',
                ];
            }

            return [
                'success' => false,
                'message' => 'خطا در دریافت وضعیت: ' . $responseBody,
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
            $data = [
                'username' => $this->username,
                'password' => $this->password, // APIKey
            ];

            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/GetCredit', $data);
            $responseBody = trim($response->body());

            if ($response->successful() && is_numeric($responseBody)) {
                return [
                    'success' => true,
                    'credit' => (float)$responseBody,
                ];
            }

            return [
                'success' => false,
                'credit' => 0,
                'message' => $responseBody,
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
