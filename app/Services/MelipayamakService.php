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

    /**
     * دریافت لیست تمامی الگوها (پترن‌های) درج شده
     * بر اساس مستندات: https://api.payamak-panel.com/post/SharedService.asmx
     * 
     * @return array
     */
    public function getSharedServiceBody()
    {
        try {
            Log::debug('Melipayamak GetSharedServiceBody Request', [
                'username' => $this->username,
            ]);

            // استفاده از GET با query parameters برای متد GetSharedServiceBody
            // بر اساس مستندات: https://api.payamak-panel.com/post/SharedService.asmx/GetSharedServiceBody?username=&password=
            $baseUrl = 'https://api.payamak-panel.com/post/SharedService.asmx/GetSharedServiceBody';
            
            // ارسال درخواست GET با query parameters
            $response = Http::get($baseUrl, [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            $responseBody = trim($response->body());
            $httpStatus = $response->status();
            
            Log::debug('Melipayamak GetSharedServiceBody Response', [
                'response_body' => substr($responseBody, 0, 500), // فقط 500 کاراکتر اول برای لاگ
                'http_status' => $httpStatus,
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'خطا در دریافت الگوها: ' . $responseBody,
                    'patterns' => [],
                    'api_response' => $responseBody,
                    'http_status_code' => $httpStatus,
                    'raw_response' => $responseBody,
                ];
            }

            // تلاش برای پارس XML پاسخ
            $patterns = [];
            
            // اگر پاسخ XML است، باید آن را پارس کنیم
            if (strpos($responseBody, '<?xml') !== false || strpos($responseBody, '<') !== false) {
                // استفاده از SimpleXML برای پارس XML
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($responseBody);
                
                if ($xml !== false) {
                    // تبدیل XML به آرایه با حفظ ساختار
                    $json = json_encode($xml, JSON_UNESCAPED_UNICODE);
                    $data = json_decode($json, true);
                    
                    Log::debug('Melipayamak GetSharedServiceBody Parsed XML', [
                        'data_structure' => array_keys($data ?? []),
                    ]);
                    
                    // استخراج الگوها از ساختار XML
                    // بررسی ساختارهای مختلف ممکن
                    $messages = [];
                    
                    // ساختار واقعی API: ArrayOfShareServiceBody -> ShareServiceBody (آرایه)
                    if (isset($data['ShareServiceBody'])) {
                        $messages = $data['ShareServiceBody'];
                        // اگر فقط یک پیام است (نه آرایه)، آن را به آرایه تبدیل می‌کنیم
                        if (isset($messages['BodyID']) || isset($messages['BodyId'])) {
                            $messages = [$messages];
                        }
                    }
                    // ساختار قدیمی 1: GetSharedServiceBodyResult -> MessagesBL -> MessagesBL (آرایه)
                    elseif (isset($data['GetSharedServiceBodyResult']['MessagesBL']['MessagesBL'])) {
                        $messages = $data['GetSharedServiceBodyResult']['MessagesBL']['MessagesBL'];
                    }
                    // ساختار قدیمی 2: GetSharedServiceBodyResult -> MessagesBL (آرایه مستقیم)
                    elseif (isset($data['GetSharedServiceBodyResult']['MessagesBL'])) {
                        $messages = $data['GetSharedServiceBodyResult']['MessagesBL'];
                    }
                    // ساختار قدیمی 3: GetSharedServiceBodyResult -> MessageBL
                    elseif (isset($data['GetSharedServiceBodyResult']['MessageBL'])) {
                        $messages = $data['GetSharedServiceBodyResult']['MessageBL'];
                    }
                    // ساختار قدیمی 4: MessagesBL مستقیم
                    elseif (isset($data['MessagesBL'])) {
                        $messages = $data['MessagesBL'];
                    }
                    
                    // اگر فقط یک پیام است (نه آرایه)، آن را به آرایه تبدیل می‌کنیم
                    if (!empty($messages) && !is_numeric(key($messages)) && (isset($messages['BodyID']) || isset($messages['BodyId']))) {
                        $messages = [$messages];
                    }
                    
                    // پردازش هر پیام
                    if (is_array($messages) && !empty($messages)) {
                        foreach ($messages as $message) {
                            if (is_array($message)) {
                                // استخراج فیلدها - ساختار واقعی: BodyID, Title, Body, BodyStatus, InsertDate, Description
                                $patternCode = $message['BodyID'] ?? $message['BodyId'] ?? $message['bodyId'] ?? $message['bodyID'] ?? $message['PatternCode'] ?? $message['patternCode'] ?? null;
                                $title = $message['Title'] ?? $message['title'] ?? '';
                                $text = $message['Body'] ?? $message['body'] ?? $message['Text'] ?? $message['text'] ?? '';
                                $bodyStatus = $message['BodyStatus'] ?? $message['bodyStatus'] ?? $message['Status'] ?? $message['status'] ?? '1';
                                $insertDate = $message['InsertDate'] ?? $message['insertDate'] ?? null;
                                $description = $message['Description'] ?? $message['description'] ?? '';
                                
                                // تبدیل به رشته برای pattern_code
                                if ($patternCode !== null) {
                                    $patternCode = (string)$patternCode;
                                }
                                
                                // تبدیل BodyStatus به وضعیت ما
                                // BodyStatus: 1 = تایید شده، 5 = رد شده یا در انتظار
                                $status = 'approved';
                                if ($bodyStatus == '5' || $bodyStatus == 5) {
                                    $status = 'rejected';
                                } elseif ($bodyStatus == '0' || $bodyStatus == 0) {
                                    $status = 'pending';
                                }
                                
                                $patterns[] = [
                                    'pattern_code' => $patternCode,
                                    'title' => $title,
                                    'text' => $text,
                                    'blacklist_id' => '1', // طبق مستندات، blackListId باید 1 باشد
                                    'status' => $status,
                                ];
                            }
                        }
                    }
                } else {
                    // اگر XML پارس نشد، خطاها را لاگ می‌کنیم
                    $errors = libxml_get_errors();
                    Log::warning('Melipayamak GetSharedServiceBody XML Parse Errors', [
                        'errors' => array_map(function($error) {
                            return $error->message;
                        }, $errors),
                    ]);
                    libxml_clear_errors();
                }
            }
            
            // اگر نتوانستیم XML را پارس کنیم، سعی می‌کنیم JSON را بررسی کنیم
            if (empty($patterns)) {
                $jsonData = json_decode($responseBody, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                    if (isset($jsonData['GetSharedServiceBodyResult'])) {
                        $result = $jsonData['GetSharedServiceBodyResult'];
                        if (isset($result['MessagesBL']) || isset($result['MessageBL'])) {
                            $messages = $result['MessagesBL'] ?? $result['MessageBL'] ?? [];
                            if (isset($messages['BodyId']) || isset($messages['PatternCode'])) {
                                $messages = [$messages];
                            }
                            foreach ($messages as $message) {
                                $patternCode = $message['BodyId'] ?? $message['PatternCode'] ?? null;
                                $patterns[] = [
                                    'pattern_code' => $patternCode ? (string)$patternCode : null,
                                    'title' => $message['Title'] ?? '',
                                    'text' => $message['Body'] ?? $message['Text'] ?? '',
                                    'blacklist_id' => isset($message['BlackListId']) ? (string)$message['BlackListId'] : '1',
                                    'status' => 'approved',
                                ];
                            }
                        }
                    }
                }
            }
            
            // اگر هنوز الگویی پیدا نکردیم، لاگ می‌کنیم
            if (empty($patterns)) {
                Log::warning('Melipayamak GetSharedServiceBody No Patterns Found', [
                    'response_preview' => substr($responseBody, 0, 1000),
                ]);
            }

            return [
                'success' => true,
                'patterns' => $patterns,
                'message' => count($patterns) . ' الگو دریافت شد',
                'api_response' => $responseBody,
                'http_status_code' => $httpStatus,
                'raw_response' => $responseBody,
            ];
            
        } catch (\Exception $e) {
            Log::error('Melipayamak GetSharedServiceBody Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس: ' . $e->getMessage(),
                'patterns' => [],
                'api_response' => null,
                'http_status_code' => null,
                'raw_response' => $e->getMessage(),
            ];
        }
    }

    /**
     * درج الگو (پترن) جدید
     * بر اساس مستندات: https://api.payamak-panel.com/post/SharedService.asmx
     * 
     * @param string $title عنوان الگو
     * @param string $body متن الگو به همراه متغیرها
     * @param int $blackListId کد لیست سیاه (معمولاً 1)
     * @return array
     */
    public function sharedServiceBodyAdd($title, $body, $blackListId = 1)
    {
        try {
            if (empty(trim($title))) {
                return [
                    'success' => false,
                    'message' => 'عنوان الگو نمی‌تواند خالی باشد',
                ];
            }

            if (empty(trim($body))) {
                return [
                    'success' => false,
                    'message' => 'متن الگو نمی‌تواند خالی باشد',
                ];
            }

            Log::debug('Melipayamak SharedServiceBodyAdd Request', [
                'title' => $title,
                'body_length' => strlen($body),
                'blackListId' => $blackListId,
                'username' => $this->username,
            ]);

            // استفاده از GET با query parameters برای متد SharedServiceBodyAdd
            // بر اساس مستندات: https://api.payamak-panel.com/post/SharedService.asmx/SharedServiceBodyAdd
            $baseUrl = 'https://api.payamak-panel.com/post/SharedService.asmx/SharedServiceBodyAdd';
            
            // ارسال درخواست GET با query parameters
            $response = Http::get($baseUrl, [
                'username' => $this->username,
                'password' => $this->password,
                'title' => trim($title),
                'body' => trim($body),
                'blackListId' => (int)$blackListId,
            ]);

            $responseBody = trim($response->body());
            $httpStatus = $response->status();
            
            Log::debug('Melipayamak SharedServiceBodyAdd Response', [
                'response_body' => $responseBody,
                'http_status' => $httpStatus,
            ]);

            // استخراج BodyId از پاسخ
            $bodyId = null;
            
            // اگر پاسخ فقط یک عدد است
            if (is_numeric(trim($responseBody))) {
                $bodyId = trim($responseBody);
            }
            // تلاش برای استخراج از تگ XML
            elseif (preg_match('/<SharedServiceBodyAddResult[^>]*>(-?\d+)<\/SharedServiceBodyAddResult>/i', $responseBody, $matches)) {
                $bodyId = $matches[1];
            }
            elseif (preg_match('/<ReturnValue[^>]*>(-?\d+)<\/ReturnValue>/i', $responseBody, $matches)) {
                $bodyId = $matches[1];
            }
            elseif (preg_match('/<[^>]*>(-?\d+)<\/[^>]*>/', $responseBody, $matches)) {
                $bodyId = $matches[1];
            }

            // بررسی نتیجه
            if ($bodyId !== null) {
                $bodyIdInt = (int)$bodyId;
                
                // اگر کد 5 یا 6 رقمی باشد (عدد مثبت)
                if ($bodyIdInt > 0) {
                    return [
                        'success' => true,
                        'body_id' => $bodyId,
                        'pattern_code' => $bodyId, // BodyId همان pattern_code است
                        'message' => 'الگو با موفقیت ایجاد شد (کد: ' . $bodyId . ')',
                        'api_response' => $responseBody,
                        'http_status_code' => $httpStatus,
                        'raw_response' => $responseBody,
                    ];
                } 
                // اگر -2 باشد، یعنی شناسه لیست سیاه اشتباه است
                elseif ($bodyIdInt === -2) {
                    return [
                        'success' => false,
                        'message' => 'شناسه لیست سیاه ویژه اشتباه است',
                        'api_response' => $responseBody,
                        'http_status_code' => $httpStatus,
                        'raw_response' => $responseBody,
                    ];
                }
                // اگر 0 باشد، یعنی نام کاربری یا رمز عبور اشتباه است
                elseif ($bodyIdInt === 0) {
                    return [
                        'success' => false,
                        'message' => 'نام کاربری و یا رمز عبور اشتباه است',
                        'api_response' => $responseBody,
                        'http_status_code' => $httpStatus,
                        'raw_response' => $responseBody,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'پاسخ نامعتبر از سرور. پاسخ: ' . substr($responseBody, 0, 200),
                'api_response' => $responseBody,
                'http_status_code' => $httpStatus,
                'raw_response' => $responseBody,
            ];
            
        } catch (\Exception $e) {
            Log::error('Melipayamak SharedServiceBodyAdd Exception', [
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

    /**
     * ویرایش الگو (پترن) موجود
     * بر اساس مستندات: https://api.payamak-panel.com/post/SharedService.asmx
     * 
     * @param int $bodyId شناسه الگو (BodyId)
     * @param string $title عنوان الگو
     * @param string $body متن الگو به همراه متغیرها
     * @param int $blackListId کد لیست سیاه (معمولاً 1)
     * @return array
     */
    public function sharedServiceBodyEdit($bodyId, $title, $body, $blackListId = 1)
    {
        try {
            if (empty(trim($title))) {
                return [
                    'success' => false,
                    'message' => 'عنوان الگو نمی‌تواند خالی باشد',
                ];
            }

            if (empty(trim($body))) {
                return [
                    'success' => false,
                    'message' => 'متن الگو نمی‌تواند خالی باشد',
                ];
            }

            Log::debug('Melipayamak SharedServiceBodyEdit Request', [
                'bodyId' => $bodyId,
                'title' => $title,
                'body_length' => strlen($body),
                'blackListId' => $blackListId,
                'username' => $this->username,
            ]);

            // استفاده از GET با query parameters برای متد SharedServiceBodyEdit
            $baseUrl = 'https://api.payamak-panel.com/post/SharedService.asmx/SharedServiceBodyEdit';
            
            // ارسال درخواست GET با query parameters
            $response = Http::get($baseUrl, [
                'username' => $this->username,
                'password' => $this->password,
                'bodyId' => (int)$bodyId,
                'title' => trim($title),
                'body' => trim($body),
                'blackListId' => (int)$blackListId,
            ]);

            $responseBody = trim($response->body());
            $httpStatus = $response->status();
            
            Log::debug('Melipayamak SharedServiceBodyEdit Response', [
                'response_body' => $responseBody,
                'http_status' => $httpStatus,
            ]);

            // استخراج نتیجه از پاسخ
            $result = null;
            
            // اگر پاسخ فقط یک عدد است
            if (is_numeric(trim($responseBody))) {
                $result = trim($responseBody);
            }
            // تلاش برای استخراج از تگ XML
            elseif (preg_match('/<SharedServiceBodyEditResult[^>]*>(-?\d+)<\/SharedServiceBodyEditResult>/i', $responseBody, $matches)) {
                $result = $matches[1];
            }
            elseif (preg_match('/<ReturnValue[^>]*>(-?\d+)<\/ReturnValue>/i', $responseBody, $matches)) {
                $result = $matches[1];
            }

            // بررسی نتیجه
            if ($result !== null) {
                $resultInt = (int)$result;
                
                // اگر 1 باشد، یعنی ویرایش موفق بوده
                if ($resultInt === 1) {
                    return [
                        'success' => true,
                        'message' => 'الگو با موفقیت ویرایش شد',
                        'api_response' => $responseBody,
                        'http_status_code' => $httpStatus,
                        'raw_response' => $responseBody,
                    ];
                } 
                // اگر -2 باشد، یعنی شناسه لیست سیاه اشتباه است
                elseif ($resultInt === -2) {
                    return [
                        'success' => false,
                        'message' => 'شناسه لیست سیاه ویژه اشتباه است',
                        'api_response' => $responseBody,
                        'http_status_code' => $httpStatus,
                        'raw_response' => $responseBody,
                    ];
                }
                // اگر 0 باشد، یعنی نام کاربری یا رمز عبور اشتباه است
                elseif ($resultInt === 0) {
                    return [
                        'success' => false,
                        'message' => 'نام کاربری و یا رمز عبور اشتباه است',
                        'api_response' => $responseBody,
                        'http_status_code' => $httpStatus,
                        'raw_response' => $responseBody,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'پاسخ نامعتبر از سرور. پاسخ: ' . substr($responseBody, 0, 200),
                'api_response' => $responseBody,
                'http_status_code' => $httpStatus,
                'raw_response' => $responseBody,
            ];
            
        } catch (\Exception $e) {
            Log::error('Melipayamak SharedServiceBodyEdit Exception', [
                'bodyId' => $bodyId,
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

    /**
     * ارسال پیامک با استفاده از الگو (Pattern)
     * متد SendByBaseNumber2
     * بر اساس مستندات: https://api.payamak-panel.com/post/send.asmx
     * 
     * @param string $to شماره گیرنده
     * @param int $bodyId کد الگو (BodyId) از ملی پیامک
     * @param array $variables آرایه متغیرها به ترتیب (مثال: ['علی', '1404/10/07'])
     * @return array
     */
    public function sendByBaseNumber2($to, $bodyId, $variables = [])
    {
        try {
            // اعتبارسنجی شماره تلفن
            if (!$this->validatePhoneNumber($to)) {
                $normalized = $this->normalizePhoneNumber($to);
                Log::error('Melipayamak SendByBaseNumber2 Invalid Phone Number', [
                    'original' => $to,
                    'normalized' => $normalized,
                ]);
                
                return [
                    'success' => false,
                    'response_code' => '18',
                    'message' => 'شماره موبایل گیرنده نامعتبر است. شماره باید با 09 شروع شود و 11 رقم باشد.',
                ];
            }

            // نرمال‌سازی شماره تلفن
            $normalizedPhone = $this->normalizePhoneNumber($to);

            // تبدیل آرایه متغیرها به رشته با جداکننده ;
            $text = '';
            if (!empty($variables)) {
                $text = implode(';', $variables);
            }

            // ساخت URL با query parameters
            $baseUrl = 'http://api.payamak-panel.com/post/Send.asmx/SendByBaseNumber2';
            
            $params = [
                'username' => $this->username,
                'password' => $this->password,
                'text' => $text,
                'to' => $normalizedPhone,
                'bodyId' => (int)$bodyId,
            ];

            Log::debug('Melipayamak SendByBaseNumber2 Request', [
                'to' => $normalizedPhone,
                'bodyId' => $bodyId,
                'variables_count' => count($variables),
                'text' => $text,
            ]);

            // ارسال درخواست GET
            $response = Http::get($baseUrl, $params);

            $responseBody = trim($response->body());
            $httpStatus = $response->status();

            Log::debug('Melipayamak SendByBaseNumber2 Response', [
                'response_body' => $responseBody,
                'http_status' => $httpStatus,
            ]);

            // بررسی پاسخ
            // بر اساس مستندات: recId یک عدد یکتا (بیش از 15 رقم) به معنای ارسال موفق است
            // کدهای خطا: -111, -110, -109, -108, -10, -7, -6, -5, -4, -3, -2, -1, 0, 2, 6, 7, 10, 11, 12, 16, 17, 18, 19, 35
            
            $errorCodes = ['-111', '-110', '-109', '-108', '-10', '-7', '-6', '-5', '-4', '-3', '-2', '-1', '0', '2', '6', '7', '10', '11', '12', '16', '17', '18', '19', '35'];
            
            // اگر پاسخ یک عدد است
            if (is_numeric($responseBody)) {
                $responseCode = (string)$responseBody;
                
                // اگر کد خطا نباشد و عدد مثبت باشد (و بیش از 15 رقم = recId)
                if (!in_array($responseCode, $errorCodes)) {
                    $responseInt = (int)$responseCode;
                    if ($responseInt > 0) {
                        // اگر بیش از 15 رقم باشد، یعنی recId است
                        if (strlen($responseCode) > 15) {
                            return [
                                'success' => true,
                                'rec_id' => $responseCode,
                                'response_code' => $responseCode,
                                'message' => 'پیامک با موفقیت ارسال شد (RecId: ' . $responseCode . ')',
                                'raw_response' => $responseBody,
                                'api_response' => $responseBody,
                            ];
                        }
                    }
                }
            }

            // در صورت خطا
            $errorMessage = $this->getPatternErrorMessage($responseBody);
            
            Log::error('Melipayamak SendByBaseNumber2 Error', [
                'to' => $normalizedPhone,
                'bodyId' => $bodyId,
                'error' => $errorMessage,
                'response_code' => $responseBody,
                'response_body' => $responseBody,
                'http_status_code' => $httpStatus,
            ]);

            return [
                'success' => false,
                'response_code' => $responseBody,
                'message' => $errorMessage,
                'raw_response' => $responseBody,
                'api_response' => $responseBody,
            ];
        } catch (\Exception $e) {
            Log::error('Melipayamak SendByBaseNumber2 Exception', [
                'to' => $to,
                'bodyId' => $bodyId,
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
     * دریافت پیام خطا برای ارسال الگویی
     */
    protected function getPatternErrorMessage($responseCode)
    {
        $errorMessages = [
            '-111' => 'IP درخواست کننده نامعتبر است',
            '-110' => 'الزام استفاده از ApiKey به جای رمز عبور',
            '-109' => 'الزام تنظیم IP مجاز برای استفاده از API',
            '-108' => 'مسدود شدن IP به دلیل تلاش ناموفق استفاده از API',
            '-10' => 'در میان متغیرهای ارسالی، لینک وجود دارد',
            '-7' => 'خطایی در شماره فرستنده رخ داده است. با پشتیبانی تماس بگیرید',
            '-6' => 'خطای داخلی رخ داده است. با پشتیبانی تماس بگیرید',
            '-5' => 'متن ارسالی با توجه به متغیرهای مشخص شده در متن پیش‌فرض همخوانی ندارد',
            '-4' => 'کد متن ارسالی صحیح نمی‌باشد و یا توسط مدیر سامانه تأیید نشده است',
            '-3' => 'خط ارسالی در سیستم تعریف نشده است. با پشتیبانی سامانه تماس بگیرید',
            '-2' => 'محدودیت تعداد شماره. محدودیت هر بار ارسال یک شماره موبایل می‌باشد',
            '-1' => 'دسترسی برای استفاده از این وب‌سرویس غیرفعال است. با پشتیبانی تماس بگیرید',
            '0' => 'نام کاربری یا رمزعبور صحیح نمی‌باشد',
            '2' => 'اعتبار کافی نمی‌باشد',
            '6' => 'سامانه در حال بروزرسانی می‌باشد',
            '7' => 'متن حاوی کلمه فیلتر شده می‌باشد. با واحد اداری تماس بگیرید',
            '10' => 'کاربر موردنظر فعال نمی‌باشد',
            '11' => 'ارسال نشده',
            '12' => 'مدارک کاربر کامل نمی‌باشد',
            '16' => 'شماره گیرنده‌ای یافت نشد',
            '17' => 'متن پیامک خالی می‌باشد',
            '18' => 'شماره گیرنده نامعتبر است',
            '19' => 'از محدودیت ساعتی فراتر رفته‌اید',
            '35' => 'شماره موبایل گیرنده در لیست سیاه مخابرات است',
        ];

        $code = (string)$responseCode;
        return $errorMessages[$code] ?? 'خطای نامشخص (کد: ' . $code . ')';
    }
}
