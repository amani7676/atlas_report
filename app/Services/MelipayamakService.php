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
     * اعتبارسنجی و نرمال‌سازی شماره تلفن
     */
    protected function normalizePhoneNumber($phone)
    {
        // حذف فاصله‌ها، خط تیره و کاراکترهای غیرعددی
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // حذف پیش‌شماره کشور (98) در صورت وجود
        if (strlen($phone) == 13 && substr($phone, 0, 2) == '98') {
            $phone = '0' . substr($phone, 2);
        }
        
        // اطمینان از شروع با 09
        if (strlen($phone) == 10 && substr($phone, 0, 1) == '9') {
            $phone = '0' . $phone;
        }
        
        return $phone;
    }

    /**
     * اعتبارسنجی شماره تلفن
     */
    protected function validatePhoneNumber($phone)
    {
        $normalized = $this->normalizePhoneNumber($phone);
        
        // شماره باید با 09 شروع شود و دقیقاً 11 رقم باشد
        if (strlen($normalized) !== 11 || substr($normalized, 0, 2) !== '09') {
            return false;
        }
        
        // بررسی اینکه همه ارقام عدد هستند
        return ctype_digit($normalized);
    }

    /**
     * اعتبارسنجی متن پیام
     */
    protected function validateMessageText($text)
    {
        // متن نباید خالی باشد
        if (empty(trim($text))) {
            return false;
        }
        
        // بررسی طول متن (حداکثر 1000 کاراکتر)
        if (mb_strlen($text) > 1000) {
            return false;
        }
        
        return true;
    }

    /**
     * ارسال پیامک به یک شماره
     */
    public function sendSms($to, $from, $text)
    {
        try {
            // اعتبارسنجی شماره تلفن
            if (!$this->validatePhoneNumber($to)) {
                $normalized = $this->normalizePhoneNumber($to);
                Log::error('Melipayamak Invalid Phone Number', [
                    'original' => $to,
                    'normalized' => $normalized,
                ]);
                
                return [
                    'success' => false,
                    'response_code' => '18',
                    'message' => 'شماره موبایل گیرنده نامعتبر است. شماره باید با 09 شروع شود و 11 رقم باشد. (شماره وارد شده: ' . $to . ')',
                ];
            }

            // اعتبارسنجی متن پیام
            if (!$this->validateMessageText($text)) {
                Log::error('Melipayamak Invalid Message Text', [
                    'text_length' => mb_strlen($text),
                    'text_preview' => mb_substr($text, 0, 50),
                ]);
                
                return [
                    'success' => false,
                    'response_code' => '17',
                    'message' => 'متن پیامک خالی است یا متغیر text مقدار ندارد. متن نباید خالی باشد و حداکثر 1000 کاراکتر باشد.',
                ];
            }

            // نرمال‌سازی شماره تلفن
            $normalizedPhone = $this->normalizePhoneNumber($to);

            $data = [
                'username' => $this->username,
                'password' => $this->password, // APIKey
                'to' => $normalizedPhone,
                'from' => $from,
                'text' => trim($text),
            ];

            // لاگ داده‌های ارسالی (بدون نمایش رمز عبور)
            Log::debug('Melipayamak SMS Request', [
                'to' => $normalizedPhone,
                'from' => $from,
                'text_length' => mb_strlen($text),
            ]);

            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/SendSMS', $data);

            $responseBody = trim($response->body());
            
            // بررسی اینکه آیا پاسخ JSON است یا رشته عددی
            $responseData = null;
            $responseCode = null;
            $responseType = 'unknown';
            
            // تلاش برای پارس JSON
            $jsonData = json_decode($responseBody, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                // پاسخ JSON است
                $responseType = 'json';
                $responseCode = isset($jsonData['RetStatus']) ? (string)$jsonData['RetStatus'] : (string)$jsonData['Value'];
                $responseData = $jsonData;
            } else {
                // پاسخ رشته عددی است
                $responseType = 'numeric';
                $responseCode = (string)$responseBody;
            }

            // بررسی موفقیت آمیز بودن ارسال
            // بر اساس مستندات رسمی ملی پیامک:
            // - کد 1: ارسال پیامک با موفقیت انجام شد
            // - رشته عددی (recId): شناسه یکتای ارسال پیامک است و نشان‌دهندۀ ارسال موفق می‌باشد
            // کدهای خطا: -1, 0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 35, 108, 109, 110
            $errorCodes = ['-1', '0', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '14', '15', '16', '17', '18', '35', '108', '109', '110'];
            
            if ($response->successful() && is_numeric($responseCode)) {
                $responseInt = (int)$responseCode;
                
                // کد 1 = ارسال موفق
                if ($responseInt === 1) {
                    return [
                        'success' => true,
                        'rec_id' => 1,
                        'response_code' => $responseCode,
                        'message' => 'پیامک با موفقیت ارسال شد',
                        'raw_response' => $responseBody,
                        'api_response' => $responseData ?? $responseBody,
                    ];
                }
                
                // اگر کد خطا نباشد و عدد مثبت باشد، یعنی RecId است
                if (!in_array($responseCode, $errorCodes) && $responseInt > 0) {
                    return [
                        'success' => true,
                        'rec_id' => $responseInt,
                        'response_code' => $responseCode,
                        'message' => 'پیامک با موفقیت ارسال شد (RecId: ' . $responseCode . ')',
                        'raw_response' => $responseBody,
                        'api_response' => $responseData ?? $responseBody,
                    ];
                }
            }

            // در صورت خطا
            $errorMessage = $this->getErrorMessage($responseCode);
            
            Log::error('Melipayamak SMS Error', [
                'to' => $normalizedPhone,
                'from' => $from,
                'error' => $errorMessage,
                'response_code' => $responseCode,
                'response_type' => $responseType, // نوع پاسخ: 'json' یا 'numeric'
                'response_body' => $responseBody,
                'response_data' => $responseData,
                'http_status_code' => $response->status(),
                'request_data' => [
                    'to' => $normalizedPhone,
                    'from' => $from,
                    'text_length' => mb_strlen($text),
                ],
            ]);

            return [
                'success' => false,
                'response_code' => $responseCode,
                'message' => $errorMessage,
                'raw_response' => $responseBody,
                'api_response' => $responseData ?? $responseBody,
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
            '-1' => 'خطای نامشخص؛ برای بررسی با پشتیبانی تماس بگیرید',
            '0' => 'اتصال به وب‌سرویس ممکن نیست یا نام کاربری و رمز عبور اشتباه است',
            '2' => 'اعتبار پنل پیامک کافی نیست؛ موجودی خود را افزایش دهید',
            '3' => 'محدودیت در تعداد ارسال روزانه فعال است',
            '4' => 'محدودیت در حجم یا تعداد پیامک‌های ارسالی وجود دارد',
            '5' => 'شماره فرستنده یا سرشماره پیامکی معتبر نیست',
            '6' => 'سامانه در حال بروزرسانی است؛ بعداً تلاش کنید',
            '7' => 'متن پیامک شامل کلمه یا عبارت فیلترشده است',
            '8' => 'تعداد پیامک‌ها کمتر از حداقل مجاز برای ارسال است',
            '9' => 'ارسال از خطوط عمومی از طریق وب‌سرویس مجاز نیست',
            '10' => 'پنل پیامکی غیرفعال یا مسدود شده است',
            '11' => 'ارسال انجام نشد؛ شماره گیرنده در لیست سیاه مخابرات است',
            '12' => 'مدارک احراز هویت کاربر کامل نیست',
            '14' => 'سرشماره فرستنده امکان ارسال پیامک حاوی لینک را ندارد',
            '15' => 'در پیام‌های چندگیرنده، عبارت «لغو11» در انتهای متن نوشته نشده است',
            '16' => 'شماره موبایل گیرنده یافت نشد؛ پارامتر to را بررسی کنید',
            '17' => 'متن پیامک خالی است یا متغیر text مقدار ندارد',
            '18' => 'شماره موبایل گیرنده نامعتبر است',
            '35' => 'در متد REST، شماره گیرنده در لیست سیاه مخابرات قرار دارد',
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
            // اعتبارسنجی متن پیام
            if (!$this->validateMessageText($text)) {
                Log::error('Melipayamak Bulk SMS Invalid Message Text', [
                    'text_length' => mb_strlen($text),
                ]);
                
                return [
                    'success' => false,
                    'response_code' => '17',
                    'message' => 'متن پیامک خالی است یا متغیر text مقدار ندارد. متن نباید خالی باشد و حداکثر 1000 کاراکتر باشد.',
                ];
            }

            // تبدیل آرایه به رشته با کاما و نرمال‌سازی شماره‌ها
            if (is_array($to)) {
                $normalizedPhones = [];
                foreach ($to as $phone) {
                    if ($this->validatePhoneNumber($phone)) {
                        $normalizedPhones[] = $this->normalizePhoneNumber($phone);
                    }
                }
                
                if (empty($normalizedPhones)) {
                    return [
                        'success' => false,
                        'response_code' => '18',
                        'message' => 'هیچ شماره موبایل معتبری یافت نشد.',
                    ];
                }
                
                $to = implode(',', $normalizedPhones);
            } else {
                // اگر رشته است، شماره‌ها را با کاما جدا کرده و نرمال‌سازی می‌کنیم
                $phones = explode(',', $to);
                $normalizedPhones = [];
                foreach ($phones as $phone) {
                    $phone = trim($phone);
                    if ($this->validatePhoneNumber($phone)) {
                        $normalizedPhones[] = $this->normalizePhoneNumber($phone);
                    }
                }
                
                if (empty($normalizedPhones)) {
                    return [
                        'success' => false,
                        'response_code' => '18',
                        'message' => 'هیچ شماره موبایل معتبری یافت نشد.',
                    ];
                }
                
                $to = implode(',', $normalizedPhones);
            }

            $data = [
                'username' => $this->username,
                'password' => $this->password, // APIKey
                'to' => $to,
                'from' => $from,
                'text' => trim($text),
            ];

            Log::debug('Melipayamak Bulk SMS Request', [
                'to' => $to,
                'from' => $from,
                'text_length' => mb_strlen($text),
            ]);

            $response = Http::asForm()->post($this->baseUrl . '/SendSMS/SendSMS', $data);

            $responseBody = trim($response->body());
            
            // بررسی اینکه آیا پاسخ JSON است یا رشته عددی
            $responseData = null;
            $responseCode = null;
            $responseType = 'unknown';
            
            // تلاش برای پارس JSON
            $jsonData = json_decode($responseBody, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                // پاسخ JSON است
                $responseType = 'json';
                $responseCode = isset($jsonData['RetStatus']) ? (string)$jsonData['RetStatus'] : (string)$jsonData['Value'];
                $responseData = $jsonData;
            } else {
                // پاسخ رشته عددی است
                $responseType = 'numeric';
                $responseCode = (string)$responseBody;
            }
            
            // بررسی موفقیت آمیز بودن ارسال
            // بر اساس مستندات رسمی ملی پیامک:
            // - کد 1: ارسال پیامک با موفقیت انجام شد
            // - رشته عددی (recId): شناسه یکتای ارسال پیامک است و نشان‌دهندۀ ارسال موفق می‌باشد
            // کدهای خطا: -1, 0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 35, 108, 109, 110
            $errorCodes = ['-1', '0', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '14', '15', '16', '17', '18', '35', '108', '109', '110'];

            if ($response->successful() && is_numeric($responseCode)) {
                $responseInt = (int)$responseCode;
                
                // کد 1 = ارسال موفق
                if ($responseInt === 1) {
                    return [
                        'success' => true,
                        'rec_id' => 1,
                        'response_code' => $responseCode,
                        'message' => 'پیامک‌ها با موفقیت ارسال شدند',
                        'raw_response' => $responseBody,
                        'api_response' => $responseData ?? $responseBody,
                    ];
                }
                
                // اگر کد خطا نباشد و عدد مثبت باشد، یعنی RecId است
                if (!in_array($responseCode, $errorCodes) && $responseInt > 0) {
                    return [
                        'success' => true,
                        'rec_id' => $responseInt,
                        'response_code' => $responseCode,
                        'message' => 'پیامک‌ها با موفقیت ارسال شدند (RecId: ' . $responseCode . ')',
                        'raw_response' => $responseBody,
                        'api_response' => $responseData ?? $responseBody,
                    ];
                }
            }

            $errorMessage = $this->getErrorMessage($responseCode);
            
            Log::error('Melipayamak Bulk SMS Error', [
                'to' => $to,
                'from' => $from,
                'error' => $errorMessage,
                'response_code' => $responseCode,
                'response_type' => $responseType, // نوع پاسخ: 'json' یا 'numeric'
                'response_body' => $responseBody,
                'response_data' => $responseData,
            ]);

            return [
                'success' => false,
                'response_code' => $responseCode,
                'message' => $errorMessage,
                'raw_response' => $responseBody,
                'api_response' => $responseData ?? $responseBody,
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

    /**
     * درج لیست سیاه برای استفاده در ارسال پیامک از طریق الگو (پترن)
     * بر اساس مستندات: https://api.payamak-panel.com/post/blacklist.asmx
     * 
     * @param string $title عنوان لیست ویژه سیاه
     * @return array
     */
    public function blackListAdd($title)
    {
        try {
            if (empty(trim($title))) {
                return [
                    'success' => false,
                    'message' => 'عنوان لیست سیاه نمی‌تواند خالی باشد',
                ];
            }

            Log::debug('Melipayamak BlackListAdd Request', [
                'title' => $title,
                'username' => $this->username,
            ]);

            // استفاده از GET با query parameters برای متد BlackListAdd
            // بر اساس مستندات: https://api.payamak-panel.com/post/blacklist.asmx/BlackListAdd?username=""&password=""&title="test"
            $baseUrl = 'https://api.payamak-panel.com/post/blacklist.asmx/BlackListAdd';
            
            // ارسال درخواست GET با query parameters
            $response = Http::get($baseUrl, [
                'username' => $this->username,
                'password' => $this->password,
                'title' => trim($title),
            ]);

            $responseBody = trim($response->body());
            $httpStatus = $response->status();
            
            Log::debug('Melipayamak BlackListAdd Response', [
                'response_body' => $responseBody,
                'http_status' => $httpStatus,
            ]);

            // استخراج BlackListId از پاسخ
            // پاسخ می‌تواند XML یا عدد ساده باشد
            $blackListId = null;
            
            // اگر پاسخ فقط یک عدد است (فرمت ساده)
            if (is_numeric(trim($responseBody))) {
                $blackListId = trim($responseBody);
            }
            // تلاش برای استخراج از تگ BlackListAddResult در XML
            elseif (preg_match('/<BlackListAddResult[^>]*>(\d+)<\/BlackListAddResult>/i', $responseBody, $matches)) {
                $blackListId = $matches[1];
            }
            // تلاش برای استخراج از تگ ReturnValue در XML
            elseif (preg_match('/<ReturnValue[^>]*>(\d+)<\/ReturnValue>/i', $responseBody, $matches)) {
                $blackListId = $matches[1];
            }
            // تلاش برای استخراج هر عددی که در تگ XML باشد
            elseif (preg_match('/<[^>]*>(\d+)<\/[^>]*>/', $responseBody, $matches)) {
                $blackListId = $matches[1];
            }

            // بررسی نتیجه
            if ($blackListId !== null) {
                $blackListIdInt = (int)$blackListId;
                
                // اگر کد 5 رقمی باشد (عدد مثبت)
                if ($blackListIdInt > 0) {
                    return [
                        'success' => true,
                        'blacklist_id' => $blackListId,
                        'message' => 'لیست سیاه با موفقیت ایجاد شد (کد: ' . $blackListId . ')',
                        'api_response' => $responseBody,
                        'http_status_code' => $httpStatus,
                        'raw_response' => $responseBody,
                    ];
                } 
                // اگر 0 باشد، یعنی نام کاربری یا رمز عبور اشتباه است
                elseif ($blackListIdInt === 0) {
                    return [
                        'success' => false,
                        'message' => 'نام کاربری و یا رمز عبور اشتباه است',
                        'api_response' => $responseBody,
                        'http_status_code' => $httpStatus,
                        'raw_response' => $responseBody,
                    ];
                }
            }

            // اگر نتوانستیم BlackListId را استخراج کنیم
            return [
                'success' => false,
                'message' => 'پاسخ نامعتبر از سرور. پاسخ: ' . substr($responseBody, 0, 200),
                'api_response' => $responseBody,
                'http_status_code' => $httpStatus,
                'raw_response' => $responseBody,
            ];
            
        } catch (\Exception $e) {
            Log::error('Melipayamak BlackListAdd Exception', [
                'title' => $title,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس: ' . $e->getMessage(),
                'api_response' => null,
                'http_status_code' => null,
                'raw_response' => $e->getMessage(),
            ];
        }
    }
}
