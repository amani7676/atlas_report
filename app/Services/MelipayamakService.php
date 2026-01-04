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
        // اول از دیتابیس می‌خوانیم، سپس از config
        $this->password = \App\Models\ApiKey::getKeyValue('api_key') 
            ?: config('services.melipayamak.api_key') 
            ?: config('services.melipayamak.password');
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
            Log::debug('Melipayamak GetSharedServiceBody Request (SOAP)', [
                'username' => $this->username,
                'has_password' => !empty($this->password),
                'password_length' => strlen($this->password ?? ''),
            ]);

            // استفاده از SOAP API بر اساس مستندات
            // WSDL: https://api.payamak-panel.com/post/SharedService.asmx?wsdl
            $wsdlUrl = 'https://api.payamak-panel.com/post/SharedService.asmx?wsdl';
            
            // بررسی اینکه SOAP extension فعال است
            if (!extension_loaded('soap')) {
                Log::error('SOAP extension is not loaded');
                return [
                    'success' => false,
                    'message' => 'افزونه SOAP در PHP فعال نیست. لطفاً آن را در php.ini فعال کنید.',
                    'patterns' => [],
                    'api_response' => null,
                    'http_status_code' => null,
                    'raw_response' => 'SOAP extension not available',
                ];
            }

            // بررسی اینکه username و password (API Key) تنظیم شده‌اند
            if (empty($this->username) || empty($this->password)) {
                Log::error('Melipayamak credentials not set', [
                    'has_username' => !empty($this->username),
                    'has_password' => !empty($this->password),
                ]);
                return [
                    'success' => false,
                    'message' => 'نام کاربری یا API Key تنظیم نشده است. لطفاً تنظیمات را بررسی کنید.',
                    'patterns' => [],
                    'api_response' => null,
                    'http_status_code' => null,
                    'raw_response' => 'Credentials not set',
                ];
            }

            // ساخت داده‌های SOAP
            // توجه: $this->password در واقع API Key است (نه password واقعی)
            $soapData = [
                'username' => $this->username,
                'password' => $this->password, // این در واقع API Key است
            ];

            // ایجاد SOAP Client
            ini_set("soap.wsdl_cache_enabled", "0");
            $soapClient = new \SoapClient($wsdlUrl, [
                'encoding' => 'UTF-8',
                'cache_wsdl' => WSDL_CACHE_NONE,
                'exceptions' => true,
            ]);

            // فراخوانی متد GetSharedServiceBody
            $soapResult = $soapClient->GetSharedServiceBody($soapData);
            
            Log::debug('SOAP Raw Result', [
                'type' => gettype($soapResult),
                'is_object' => is_object($soapResult),
                'object_properties' => is_object($soapResult) ? get_object_vars($soapResult) : null,
            ]);
            
            // استخراج نتیجه
            // پاسخ SOAP می‌تواند به صورت GetSharedServiceBodyResult یا ShareServiceBody باشد
            $result = null;
            if (is_object($soapResult)) {
                // اگر نتیجه یک object است، GetSharedServiceBodyResult را استخراج می‌کنیم
                if (isset($soapResult->GetSharedServiceBodyResult)) {
                    $result = $soapResult->GetSharedServiceBodyResult;
                } elseif (isset($soapResult->ShareServiceBody)) {
                    // ساختار جدید: ShareServiceBody مستقیم
                    $result = $soapResult->ShareServiceBody;
                } else {
                    // اگر هیچکدام نیست، کل object را استفاده می‌کنیم
                    $result = $soapResult;
                }
            } else {
                $result = $soapResult;
            }
            
            // تبدیل به آرایه برای پردازش
            $json = json_encode($result, JSON_UNESCAPED_UNICODE);
            $data = json_decode($json, true);
            
            Log::debug('After JSON conversion', [
                'data_type' => gettype($data),
                'data_keys' => is_array($data) ? array_keys($data) : 'not_array',
                'is_numeric_array' => is_array($data) && !empty($data) && is_numeric(key($data)),
                'first_item_has_BodyID' => is_array($data) && !empty($data) && isset($data[0]) && isset($data[0]['BodyID']),
            ]);
            
            // اگر data خودش یک آرایه از الگوها است (مستقیم ShareServiceBody)
            // این برای زمانی است که SOAP مستقیماً آرایه برمی‌گرداند
            if (is_array($data) && !empty($data) && is_numeric(key($data)) && isset($data[0]) && (isset($data[0]['BodyID']) || isset($data[0]['BodyId']))) {
                // data مستقیماً آرایه الگوها است، آن را در ShareServiceBody قرار می‌دهیم
                $data = ['ShareServiceBody' => $data];
                Log::debug('Converted direct array to ShareServiceBody structure', [
                    'count' => count($data['ShareServiceBody']),
                ]);
            }
            
            Log::debug('Melipayamak GetSharedServiceBody Response (SOAP)', [
                'result_type' => gettype($result),
                'data_keys' => is_array($data) ? array_keys($data) : 'not_array',
                'data_preview' => substr($json, 0, 500),
                'has_ShareServiceBody' => isset($data['ShareServiceBody']),
                'ShareServiceBody_count' => isset($data['ShareServiceBody']) && is_array($data['ShareServiceBody']) ? count($data['ShareServiceBody']) : 0,
            ]);
            
            // اگر نتیجه خالی است
            if (empty($result) && empty($data)) {
                return [
                    'success' => true,
                    'message' => 'هیچ الگویی در پنل ملی پیامک یافت نشد',
                    'patterns' => [],
                    'api_response' => $json,
                    'http_status_code' => 200,
                    'raw_response' => $json,
                ];
            }

            // پردازش نتیجه SOAP
            $patterns = [];
            
            // استخراج MessagesBL از GetSharedServiceBodyResult
            $messages = [];
            
            Log::debug('Checking data structure', [
                'top_level_keys' => is_array($data) ? array_keys($data) : 'not_array',
                'data_type' => gettype($data),
            ]);
            
            // بررسی ساختارهای مختلف ممکن
            // ساختار 1: ShareServiceBody مستقیم (ساختار واقعی API که کاربر نشان داد)
            if (isset($data['ShareServiceBody'])) {
                $messages = $data['ShareServiceBody'];
                Log::debug('Found structure: ShareServiceBody direct', [
                    'count' => is_array($messages) ? count($messages) : 1,
                    'is_array' => is_array($messages),
                    'first_item_keys' => is_array($messages) && !empty($messages) ? array_keys($messages[0] ?? reset($messages)) : [],
                ]);
                // اگر فقط یک پیام است (نه آرایه)، آن را به آرایه تبدیل می‌کنیم
                if (!is_numeric(key($messages)) && (isset($messages['BodyID']) || isset($messages['BodyId']))) {
                    $messages = [$messages];
                    Log::debug('Converted single ShareServiceBody to array');
                }
            }
            // ساختار 2: GetSharedServiceBodyResult -> MessagesBL -> MessagesBL (آرایه)
            elseif (isset($data['GetSharedServiceBodyResult']['MessagesBL']['MessagesBL'])) {
                $messages = $data['GetSharedServiceBodyResult']['MessagesBL']['MessagesBL'];
                Log::debug('Found structure: GetSharedServiceBodyResult -> MessagesBL -> MessagesBL', [
                    'count' => is_array($messages) ? count($messages) : 1,
                ]);
            }
            // ساختار 3: GetSharedServiceBodyResult -> MessagesBL (آرایه مستقیم)
            elseif (isset($data['GetSharedServiceBodyResult']['MessagesBL'])) {
                $messages = $data['GetSharedServiceBodyResult']['MessagesBL'];
                Log::debug('Found structure: GetSharedServiceBodyResult -> MessagesBL', [
                    'count' => is_array($messages) ? count($messages) : 1,
                ]);
            }
            // ساختار 4: MessagesBL مستقیم در نتیجه
            elseif (isset($data['MessagesBL'])) {
                $messages = $data['MessagesBL'];
                Log::debug('Found structure: MessagesBL direct', [
                    'count' => is_array($messages) ? count($messages) : 1,
                ]);
            }
            // ساختار 5: اگر نتیجه مستقیماً یک آرایه است
            elseif (is_array($data) && !empty($data)) {
                // بررسی اینکه آیا کل داده یک آرایه از MessagesBL است
                $firstKey = array_key_first($data);
                if (is_numeric($firstKey) || (isset($data[$firstKey]['BodyID']) || isset($data[$firstKey]['BodyId']))) {
                    $messages = $data;
                    Log::debug('Found structure: Direct array', [
                        'count' => count($messages),
                    ]);
                }
            }
            
            // اگر فقط یک پیام است (نه آرایه)، آن را به آرایه تبدیل می‌کنیم
            if (!empty($messages) && !is_numeric(key($messages)) && (isset($messages['BodyID']) || isset($messages['BodyId']))) {
                $messages = [$messages];
                Log::debug('Converted single message to array');
            }
            
            // پردازش هر پیام
            if (is_array($messages) && !empty($messages)) {
                Log::debug('Processing messages', [
                    'messages_count' => count($messages),
                    'first_message_keys' => !empty($messages) && is_array($messages[0] ?? reset($messages)) ? array_keys($messages[0] ?? reset($messages)) : [],
                ]);
                
                foreach ($messages as $index => $message) {
                    if (is_array($message)) {
                        // استخراج فیلدها - ساختار واقعی: BodyID, Title, Body, BodyStatus, InsertDate, Description
                        $patternCode = $message['BodyID'] ?? $message['BodyId'] ?? $message['bodyId'] ?? $message['bodyID'] ?? $message['PatternCode'] ?? $message['patternCode'] ?? null;
                        $title = $message['Title'] ?? $message['title'] ?? '';
                        $text = $message['Body'] ?? $message['body'] ?? $message['Text'] ?? $message['text'] ?? '';
                        $bodyStatus = $message['BodyStatus'] ?? $message['bodyStatus'] ?? $message['Status'] ?? $message['status'] ?? '1';
                        $insertDate = $message['InsertDate'] ?? $message['insertDate'] ?? null;
                        $description = $message['Description'] ?? $message['description'] ?? '';
                        $blackListId = $message['BlackListId'] ?? $message['blackListId'] ?? $message['BlacklistId'] ?? '1';
                        
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
                            'blacklist_id' => (string)$blackListId,
                            'status' => $status,
                            'body_status' => $bodyStatus,
                            'insert_date' => $insertDate,
                            'description' => $description,
                        ];
                        
                        Log::debug('Pattern extracted', [
                            'index' => $index,
                            'pattern_code' => $patternCode,
                            'title' => substr($title, 0, 50),
                        ]);
                    }
                }
            } else {
                Log::warning('No messages found in SOAP result', [
                    'data_keys' => is_array($data) ? array_keys($data) : 'not_array',
                    'result_type' => gettype($result),
                ]);
            }
            
            // اگر هنوز الگویی پیدا نکردیم، لاگ می‌کنیم
            if (empty($patterns)) {
                Log::warning('Melipayamak GetSharedServiceBody No Patterns Found', [
                    'response_preview' => substr($json, 0, 1000),
                    'data_structure' => is_array($data) ? array_keys($data) : 'not_array',
                ]);
            }

            Log::info('Melipayamak GetSharedServiceBody Success', [
                'patterns_count' => count($patterns),
            ]);

            return [
                'success' => true,
                'patterns' => $patterns,
                'message' => count($patterns) . ' الگو دریافت شد',
                'api_response' => $json,
                'http_status_code' => 200,
                'raw_response' => $json,
            ];
            
        } catch (\SoapFault $e) {
            Log::error('Melipayamak GetSharedServiceBody SOAP Exception', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس SOAP: ' . $e->getMessage(),
                'patterns' => [],
                'api_response' => null,
                'http_status_code' => null,
                'raw_response' => $e->getMessage(),
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
            // تلاش برای استخراج از تگ XML (مثل <int>-1</int>)
            elseif (preg_match('/<int[^>]*>(-?\d+)<\/int>/i', $responseBody, $matches)) {
                $result = $matches[1];
            }
            // تلاش برای استخراج از تگ XML (مثل <SharedServiceBodyEditResult>-1</SharedServiceBodyEditResult>)
            elseif (preg_match('/<SharedServiceBodyEditResult[^>]*>(-?\d+)<\/SharedServiceBodyEditResult>/i', $responseBody, $matches)) {
                $result = $matches[1];
            }
            // تلاش برای استخراج از تگ XML (مثل <ReturnValue>-1</ReturnValue>)
            elseif (preg_match('/<ReturnValue[^>]*>(-?\d+)<\/ReturnValue>/i', $responseBody, $matches)) {
                $result = $matches[1];
            }
            // تلاش برای استخراج از تگ XML (مثل <string>-1</string>)
            elseif (preg_match('/<string[^>]*>(-?\d+)<\/string>/i', $responseBody, $matches)) {
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
                // اگر -1 باشد، یعنی دسترسی برای استفاده از این وب‌سرویس غیرفعال است
                elseif ($resultInt === -1) {
                    return [
                        'success' => false,
                        'message' => 'دسترسی برای استفاده از این وب‌سرویس غیرفعال است. با پشتیبانی تماس بگیرید',
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
                // سایر کدهای خطا
                else {
                    $errorMessage = $this->getPatternErrorMessage((string)$resultInt);
                    return [
                        'success' => false,
                        'message' => $errorMessage,
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
     * ارسال پیامک با استفاده از الگو (Pattern) - متد SendByBaseNumber
     * بر اساس مستندات: http://api.payamak-panel.com/post/send.asmx
     * این متد برای ارسال پیامک با متن پیشفرض از خط خدماتی اشتراکی استفاده می‌شود
     * 
     * @param string $to شماره گیرنده
     * @param int $bodyId کد الگو (BodyId) از ملی پیامک
     * @param array $variables آرایه متغیرها به ترتیب (مثال: ['علی', '1404/10/07'])
     * @return array
     */
    public function sendByBaseNumber($to, $bodyId, $variables = [], $from = null, $apiKey = null)
    {
        try {
            // اعتبارسنجی شماره تلفن
            if (!$this->validatePhoneNumber($to)) {
                $normalized = $this->normalizePhoneNumber($to);
                Log::error('Melipayamak SendByBaseNumber Invalid Phone Number', [
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

            // اطمینان از اینکه variables یک آرایه است
            if (!is_array($variables)) {
                $variables = [];
            }

            // استفاده از SOAP API
            // بر اساس مستندات: http://api.payamak-panel.com/post/send.asmx
            $wsdlUrl = 'http://api.payamak-panel.com/post/Send.asmx?wsdl';
            
            // ساخت داده‌های SOAP
            $soapData = [
                'username' => $this->username,
                'password' => $this->password, // APIKey
                'text' => $variables, // آرایه متغیرها
                'to' => $normalizedPhone,
                'bodyId' => (int)$bodyId,
            ];

            Log::debug('Melipayamak SendByBaseNumber Request (SOAP)', [
                'wsdl_url' => $wsdlUrl,
                'to' => $normalizedPhone,
                'bodyId' => $bodyId,
                'variables' => $variables,
                'variables_count' => count($variables),
            ]);

            // بررسی اینکه SOAP extension فعال است
            if (!extension_loaded('soap')) {
                Log::error('SOAP extension is not loaded');
                return [
                    'success' => false,
                    'response_code' => 'soap_not_available',
                    'message' => 'افزونه SOAP در PHP فعال نیست. لطفاً آن را در php.ini فعال کنید.',
                ];
            }

            // ایجاد SOAP Client
            ini_set("soap.wsdl_cache_enabled", "0");
            $soapClient = new \SoapClient($wsdlUrl, [
                'encoding' => 'UTF-8',
                'cache_wsdl' => WSDL_CACHE_NONE,
                'exceptions' => true,
            ]);

            // فراخوانی متد SendByBaseNumber
            $soapResult = $soapClient->SendByBaseNumber($soapData);
            
            // استخراج نتیجه
            // پاسخ SOAP می‌تواند به صورت object یا string باشد
            if (is_object($soapResult)) {
                $responseBody = isset($soapResult->SendByBaseNumberResult) 
                    ? (string)$soapResult->SendByBaseNumberResult 
                    : (string)$soapResult;
            } else {
                $responseBody = (string)$soapResult;
            }

            Log::debug('Melipayamak SendByBaseNumber Response (SOAP)', [
                'response_body' => $responseBody,
                'response_type' => gettype($responseBody),
            ]);

            // بررسی پاسخ
            // بر اساس مستندات: recId یک عدد یکتا (بیش از 15 رقم) به معنای ارسال موفق است
            $errorCodes = ['-110', '-109', '-108', '-10', '-7', '-6', '-5', '-4', '-3', '-2', '-1', '0', '2', '6', '7', '10', '11', '12', '16', '17', '18', '19'];
            
            $responseCode = trim($responseBody);
            
            // بررسی اینکه آیا کد خطا است
            if (in_array($responseCode, $errorCodes)) {
                // این یک کد خطا است
                $errorMessage = $this->getPatternErrorMessage($responseCode);
                
                Log::error('Melipayamak SendByBaseNumber Error', [
                    'to' => $normalizedPhone,
                    'bodyId' => $bodyId,
                    'error' => $errorMessage,
                    'response_code' => $responseCode,
                ]);
                
                return [
                    'success' => false,
                    'response_code' => $responseCode,
                    'message' => $errorMessage,
                    'raw_response' => $responseBody,
                    'api_response' => $responseBody,
                ];
            }
            
            // بررسی موفقیت: اگر عدد بیش از 15 رقم باشد، موفق است
            // بر اساس مستندات: recId یک عدد یکتا (بیش از 15 رقم) به معنای ارسال موفق است
            if (is_numeric($responseCode) && strlen($responseCode) > 15) {
                // ارسال موفق
                return [
                    'success' => true,
                    'rec_id' => $responseCode,
                    'response_code' => $responseCode,
                    'message' => 'پیامک با موفقیت ارسال شد (RecId: ' . $responseCode . ')',
                    'raw_response' => $responseBody,
                    'api_response' => $responseBody,
                ];
            }
            
            // اگر عدد مثبت باشد اما کمتر از 15 رقم، بررسی می‌کنیم
            // بر اساس مستندات، فقط اعداد بیش از 15 رقم recId هستند
            // اما برای اطمینان، اگر عدد مثبت بزرگ باشد (بیش از 1000) و کد خطا نباشد، احتمالاً موفق است
            if (is_numeric($responseCode) && (int)$responseCode > 1000 && strlen($responseCode) >= 4) {
                // احتمالاً موفق است (برای اطمینان بیشتر، فقط اعداد بزرگ را قبول می‌کنیم)
                return [
                    'success' => true,
                    'rec_id' => $responseCode,
                    'response_code' => $responseCode,
                    'message' => 'پیامک با موفقیت ارسال شد (RecId: ' . $responseCode . ')',
                    'raw_response' => $responseBody,
                    'api_response' => $responseBody,
                ];
            }
            
            // در صورت خطا
            $errorMessage = $this->getPatternErrorMessage($responseCode);
            
            Log::error('Melipayamak SendByBaseNumber Unknown Response', [
                'to' => $normalizedPhone,
                'bodyId' => $bodyId,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
            ]);
            
            return [
                'success' => false,
                'response_code' => $responseCode,
                'message' => $errorMessage ?: 'پاسخ نامعتبر از سرور دریافت شد: ' . $responseBody,
                'raw_response' => $responseBody,
                'api_response' => $responseBody,
            ];
        } catch (\SoapFault $e) {
            Log::error('Melipayamak SendByBaseNumber SOAP Exception', [
                'to' => $to,
                'bodyId' => $bodyId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس SOAP: ' . $e->getMessage(),
                'raw_response' => $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Melipayamak SendByBaseNumber Exception', [
                'to' => $to,
                'bodyId' => $bodyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در ارسال: ' . $e->getMessage(),
                'raw_response' => $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * ارسال پیامک با استفاده از الگو (Pattern) - متد SendByBaseNumber2
     * بر اساس مستندات: https://console.melipayamak.com/api/send/shared/{api_key}
     * 
     * @param string $to شماره گیرنده
     * @param int $bodyId کد الگو (BodyId) از ملی پیامک
     * @param array $variables آرایه متغیرها به ترتیب (مثال: ['علی', '1404/10/07'])
     * @param string|null $from شماره فرستنده (اختیاری - اگر null باشد از config استفاده می‌شود)
     * @param string|null $apiKey API Key مرتبط با شماره فرستنده (اختیاری)
     * @return array
     */
    public function sendByBaseNumber2($to, $bodyId, $variables = [], $from = null, $apiKey = null)
    {
        // ابتدا سعی می‌کنیم از Console API استفاده کنیم (اولویت اول)
        // این API از API Key استفاده می‌کند و معمولاً قابل اعتمادتر است
        $consoleResult = $this->sendByBaseNumberConsole($to, $bodyId, $variables, $from, $apiKey);
        
        // اگر Console API موفق بود یا خطای غیر از "کلید کنسول معتبر نیست" داد، نتیجه را برمی‌گردانیم
        if ($consoleResult['success']) {
            Log::info('Melipayamak SendByBaseNumber2 - Console API succeeded', [
                'to' => $to,
                'bodyId' => $bodyId,
            ]);
            return $consoleResult;
        }
        
        // اگر خطا مربوط به API Key نبود، نتیجه را برمی‌گردانیم
        $isApiKeyError = stripos($consoleResult['message'] ?? '', 'کلید کنسول') !== false || 
                         stripos($consoleResult['message'] ?? '', 'console') !== false ||
                         stripos($consoleResult['message'] ?? '', 'api key') !== false;
        
        if (!$isApiKeyError) {
            Log::warning('Melipayamak SendByBaseNumber2 - Console API failed with non-API-key error', [
                'error' => $consoleResult['message'] ?? 'Unknown error',
            ]);
            return $consoleResult;
        }
        
        // اگر خطا مربوط به API Key بود، از REST API قدیمی استفاده می‌کنیم
        Log::warning('Melipayamak SendByBaseNumber2 - Console API failed (API Key error), trying REST API', [
            'console_error' => $consoleResult['message'] ?? 'Unknown error',
        ]);
        
        $restResult = $this->sendByBaseNumberRest($to, $bodyId, $variables, $from);
        if ($restResult['success']) {
            Log::info('Melipayamak SendByBaseNumber2 - REST API succeeded', [
                'to' => $to,
                'bodyId' => $bodyId,
            ]);
            return $restResult;
        }
        
        // اگر هر دو روش کار نکردند، خطای Console API را برمی‌گردانیم (دقیق‌تر است)
        return $consoleResult;
    }
    
    /**
     * ارسال پیامک با استفاده از REST API قدیمی (api.payamak-panel.com)
     * این متد از username/password استفاده می‌کند
     */
    protected function sendByBaseNumberRest($to, $bodyId, $variables = [], $from = null)
    {
        try {
            // اعتبارسنجی شماره تلفن
            if (!$this->validatePhoneNumber($to)) {
                return [
                    'success' => false,
                    'response_code' => '18',
                    'message' => 'شماره موبایل گیرنده نامعتبر است.',
                ];
            }

            $normalizedPhone = $this->normalizePhoneNumber($to);
            if (!is_array($variables)) {
                $variables = [];
            }

            // استفاده از REST API قدیمی
            // بر اساس مستندات: https://api.payamak-panel.com/post/Send.asmx/SendByBaseNumber
            $baseUrl = 'https://api.payamak-panel.com/post/Send.asmx/SendByBaseNumber';
            
            // تبدیل متغیرها به رشته با جداکننده ;
            $text = implode(';', $variables);
            
            // دریافت شماره فرستنده
            $senderNumber = $from ?? config('services.melipayamak.pattern_from') ?? config('services.melipayamak.from');
            
            $data = [
                'username' => $this->username,
                'password' => $this->password, // APIKey
                'to' => $normalizedPhone,
                'bodyId' => (int)$bodyId,
                'text' => $text, // متغیرها با جداکننده ;
            ];
            
            // اگر شماره فرستنده تنظیم شده باشد، اضافه می‌کنیم
            if (!empty($senderNumber)) {
                $data['from'] = $senderNumber;
            }

            Log::debug('Melipayamak SendByBaseNumber REST Request', [
                'url' => $baseUrl,
                'to' => $normalizedPhone,
                'from' => $senderNumber,
                'bodyId' => $bodyId,
                'variables' => $variables,
                'text' => $text,
            ]);

            // ارسال درخواست POST با timeout 30 ثانیه
            $response = Http::timeout(30)->asForm()->post($baseUrl, $data);

            $responseBody = trim($response->body());
            $httpStatus = $response->status();

            Log::debug('Melipayamak SendByBaseNumber REST Response', [
                'response_body' => $responseBody,
                'http_status' => $httpStatus,
            ]);

            // بررسی موفقیت: اگر پاسخ یک عدد مثبت باشد، موفق است
            // اما اگر پاسخ XML است، باید آن را پارس کنیم
            $responseCode = null;
            
            // اگر پاسخ XML است
            if (strpos($responseBody, '<?xml') !== false || strpos($responseBody, '<string') !== false) {
                // استخراج مقدار از XML
                if (preg_match('/<string[^>]*>([^<]+)<\/string>/', $responseBody, $matches)) {
                    $responseCode = trim($matches[1]);
                } else {
                    // اگر نتوانستیم پارس کنیم، کل پاسخ را به عنوان خطا در نظر می‌گیریم
                    $responseCode = trim($responseBody);
                }
            } else {
                $responseCode = trim($responseBody);
            }
            
            // بررسی اینکه آیا کد خطا است یا recId
            if (is_numeric($responseCode) && (int)$responseCode > 0) {
                return [
                    'success' => true,
                    'rec_id' => $responseCode,
                    'response_code' => $responseCode,
                    'message' => 'پیامک با موفقیت ارسال شد (RecId: ' . $responseCode . ')',
                    'raw_response' => $responseBody,
                    'http_status_code' => $httpStatus,
                ];
            } else {
                // خطا در ارسال
                $errorMessage = $this->getPatternErrorMessage($responseCode);
                
                return [
                    'success' => false,
                    'response_code' => $responseCode,
                    'message' => $errorMessage,
                    'raw_response' => $responseBody,
                    'http_status_code' => $httpStatus,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Melipayamak SendByBaseNumber REST Exception', [
                'to' => $to,
                'bodyId' => $bodyId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس: ' . $e->getMessage(),
                'raw_response' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * ارسال پیامک با استفاده از Console API (console.melipayamak.com)
     * این متد از API Key استفاده می‌کند
     */
    protected function sendByBaseNumberConsole($to, $bodyId, $variables = [], $from = null, $apiKey = null)
    {
        try {
            // اعتبارسنجی شماره تلفن
            if (!$this->validatePhoneNumber($to)) {
                $normalized = $this->normalizePhoneNumber($to);
                Log::error('Melipayamak SendByBaseNumberConsole Invalid Phone Number', [
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

            // اطمینان از اینکه variables یک آرایه است
            if (!is_array($variables)) {
                $variables = [];
            }

            // استفاده از API Key ارسال شده (از شماره انتخاب شده) یا از دیتابیس/config
            if (empty($apiKey)) {
                // اول از دیتابیس می‌خوانیم
                $dbConsoleKey = \App\Models\ApiKey::getKeyValue('console_api_key');
                $dbApiKey = \App\Models\ApiKey::getKeyValue('api_key');
                $configConsoleKey = config('services.melipayamak.console_api_key');
                $configApiKey = config('services.melipayamak.api_key');
                
                Log::info('Melipayamak Console API Key sources', [
                    'db_console_key_exists' => !empty($dbConsoleKey),
                    'db_console_key_length' => $dbConsoleKey ? strlen($dbConsoleKey) : 0,
                    'db_console_key_preview' => $dbConsoleKey ? substr($dbConsoleKey, 0, 8) . '...' : 'empty',
                    'db_api_key_exists' => !empty($dbApiKey),
                    'db_api_key_length' => $dbApiKey ? strlen($dbApiKey) : 0,
                    'db_api_key_preview' => $dbApiKey ? substr($dbApiKey, 0, 8) . '...' : 'empty',
                    'config_console_key_exists' => !empty($configConsoleKey),
                    'config_api_key_exists' => !empty($configApiKey),
                ]);
                
                $apiKey = $dbConsoleKey
                    ?: $dbApiKey
                    ?: $configConsoleKey 
                    ?: $configApiKey 
                    ?: $this->password;
            }
            
            // بررسی اینکه API Key وجود دارد
            if (empty($apiKey)) {
                Log::error('Melipayamak Console API Key is empty', [
                    'config_console_api_key' => config('services.melipayamak.console_api_key'),
                    'config_api_key' => config('services.melipayamak.api_key'),
                    'config_password' => config('services.melipayamak.password'),
                ]);
                
                return [
                    'success' => false,
                    'response_code' => 'no_api_key',
                    'message' => 'API Key کنسول تنظیم نشده است. لطفاً API Key را در قسمت "مدیریت API Key" با نام "console_api_key" یا "api_key" تنظیم کنید. این API Key باید از پنل کاربری console.melipayamak.com گرفته شود.',
                ];
            }
            
            // لاگ API Key برای دیباگ (فقط 8 کاراکتر اول)
            $apiKeySource = 'unknown';
            if (!empty(\App\Models\ApiKey::getKeyValue('console_api_key'))) {
                $apiKeySource = 'db_console_api_key';
            } elseif (!empty(\App\Models\ApiKey::getKeyValue('api_key'))) {
                $apiKeySource = 'db_api_key';
            } elseif (!empty(config('services.melipayamak.console_api_key'))) {
                $apiKeySource = 'config_console_api_key';
            } elseif (!empty(config('services.melipayamak.api_key'))) {
                $apiKeySource = 'config_api_key';
            } else {
                $apiKeySource = 'password';
            }
            
            Log::info('Melipayamak Console API Key loaded', [
                'api_key_preview' => substr($apiKey, 0, 8) . '...',
                'api_key_length' => strlen($apiKey),
                'api_key_source' => $apiKeySource,
                'api_key_full' => $apiKey, // برای دیباگ - در production باید حذف شود
            ]);
            
            // URL پایه برای API کنسول
            // بر اساس مستندات: https://console.melipayamak.com/api/send/shared/{api_key}
            // توجه: API Key باید در URL قرار بگیرد (نه در header)
            // API Key معمولاً نیازی به encoding ندارد مگر اینکه کاراکترهای خاص داشته باشد
            // اما برای اطمینان، API Key را trim می‌کنیم
            $apiKey = trim($apiKey);
            $baseUrl = 'https://console.melipayamak.com/api/send/shared/' . $apiKey;
            
            Log::info('Melipayamak Console API URL constructed', [
                'base_url' => $baseUrl,
                'api_key_length' => strlen($apiKey),
                'api_key_preview' => substr($apiKey, 0, 8) . '...',
                'api_key_full' => $apiKey, // برای دیباگ - در production باید حذف شود
            ]);
            
            // دریافت شماره فرستنده
            // برای پیامک‌های الگویی، از pattern_from استفاده می‌کنیم
            $senderNumber = $from ?? config('services.melipayamak.pattern_from') ?? config('services.melipayamak.from');
            
            // ساخت داده‌های JSON برای Console API
            // بر اساس مستندات Console API: bodyId, to, args (آرایه متغیرها)
            // Console API از args به صورت آرایه استفاده می‌کند، نه text
            // اطمینان از اینکه variables یک آرایه است
            if (!is_array($variables)) {
                $variables = [];
            }
            
            // اطمینان از اینکه متغیرها به صورت آرایه هستند و مقادیر خالی نیستند
            $argsArray = [];
            foreach ($variables as $var) {
                // تبدیل به رشته و trim کردن
                $varStr = trim((string)$var);
                // اگر خالی نبود، اضافه می‌کنیم
                if ($varStr !== '') {
                    $argsArray[] = $varStr;
                } else {
                    // اگر خالی بود، یک رشته خالی اضافه می‌کنیم (برای حفظ ترتیب)
                    $argsArray[] = '';
                }
            }
            
            $data = [
                'bodyId' => (int)$bodyId,
                'to' => $normalizedPhone,
                'args' => $argsArray, // آرایه متغیرها به ترتیب (مثال: ['فرشید امانی 2', '1404/10/10'])
            ];
            
            Log::debug('Melipayamak Console API - Variables format', [
                'variables_original' => $variables,
                'variables_array' => $argsArray,
                'variables_count' => count($argsArray),
                'variables_preview' => array_slice($argsArray, 0, 5), // فقط 5 مورد اول برای لاگ
                'bodyId' => $bodyId,
                'to' => $normalizedPhone,
            ]);
            
            // توجه: پارامتر 'from' در مستندات Console API ذکر نشده است
            // بنابراین آن را ارسال نمی‌کنیم

            Log::info('Melipayamak SendByBaseNumber2 Request (Console API)', [
                'url' => $baseUrl,
                'api_key_length' => strlen($apiKey),
                'api_key_preview' => substr($apiKey, 0, 8) . '...',
                'api_key_full' => $apiKey, // برای دیباگ - در production باید حذف شود
                'to' => $normalizedPhone,
                'bodyId' => $bodyId,
                'bodyId_type' => gettype($bodyId),
                'variables_original' => $variables,
                'variables_processed' => $argsArray,
                'variables_count' => count($argsArray),
                'variables_preview' => array_slice($argsArray, 0, 5), // فقط 5 مورد اول
                'data' => $data,
                'data_json' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), // برای بررسی فرمت JSON
            ]);

            // ارسال درخواست POST با JSON
            // توجه: API Key در URL قرار می‌گیرد (نه در header)
            // افزایش timeout به 30 ثانیه برای جلوگیری از خطای timeout
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($baseUrl, $data);

            $responseBody = trim($response->body());
            $httpStatus = $response->status();

            Log::debug('Melipayamak SendByBaseNumber2 Response (New API)', [
                'response_body' => $responseBody,
                'http_status' => $httpStatus,
                'response_length' => strlen($responseBody),
            ]);

            // بررسی پاسخ API جدید
            // بر اساس مستندات: پاسخ JSON است با ساختار:
            // {
            //   "recId": 3741437414,
            //   "status": "شرح خطا در صورت بروز"
            // }
            
            $jsonData = json_decode($responseBody, true);
            $recId = null;
            $status = null;
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                // پاسخ JSON است
                $recId = isset($jsonData['recId']) ? (string)$jsonData['recId'] : null;
                $status = isset($jsonData['status']) ? trim($jsonData['status']) : null;
                
                Log::debug('New API JSON Response parsed', [
                    'recId' => $recId,
                    'status' => $status,
                ]);
                
                // بررسی موفقیت: اگر recId وجود داشته باشد و status خالی باشد یا null باشد، موفق است
                // همچنین اگر recId وجود داشته باشد و status یک رشته خالی باشد، موفق است
                if ($recId && (empty($status) || $status === null || $status === '')) {
                    // ارسال موفق
                    return [
                        'success' => true,
                        'rec_id' => $recId,
                        'response_code' => $recId,
                        'message' => 'پیامک با موفقیت ارسال شد (RecId: ' . $recId . ')',
                        'raw_response' => $responseBody,
                        'api_response' => $jsonData,
                        'http_status_code' => $httpStatus,
                        'status' => $status,
                    ];
                } else {
                    // خطا در ارسال
                    $errorMessage = $status ?: 'خطا در ارسال پیامک';
                    
                    // بررسی خطای "کلید کنسول معتبر نیست"
                    if (stripos($status, 'کلید کنسول') !== false || 
                        stripos($status, 'console') !== false ||
                        stripos($status, 'api key') !== false ||
                        stripos($status, 'api_key') !== false ||
                        stripos($responseBody, 'کلید کنسول') !== false ||
                        stripos($responseBody, 'console') !== false) {
                        $errorMessage = 'کلید کنسول معتبر نیست. لطفاً API Key را از پنل console.melipayamak.com بررسی کنید.';
                    }
                    
                    Log::error('Melipayamak SendByBaseNumber2 Error (New API)', [
                        'to' => $normalizedPhone,
                        'bodyId' => $bodyId,
                        'error' => $errorMessage,
                        'status' => $status,
                        'recId' => $recId,
                        'response_body' => $responseBody,
                        'http_status_code' => $httpStatus,
                        'api_key_used' => substr($apiKey, 0, 8) . '...',
                        'api_key_length' => strlen($apiKey),
                    ]);
                    
                    return [
                        'success' => false,
                        'response_code' => $recId ?? 'error',
                        'message' => $errorMessage,
                        'raw_response' => $responseBody,
                        'api_response' => $jsonData,
                        'http_status_code' => $httpStatus,
                        'status' => $status,
                    ];
                }
            } else {
                // اگر پاسخ JSON نبود، خطا است
                $errorMessage = 'پاسخ نامعتبر از سرور دریافت شد';
                
                Log::error('Melipayamak SendByBaseNumber2 Invalid Response', [
                    'to' => $normalizedPhone,
                    'bodyId' => $bodyId,
                    'error' => $errorMessage,
                    'response_body' => $responseBody,
                    'http_status_code' => $httpStatus,
                ]);
                
                return [
                    'success' => false,
                    'response_code' => 'invalid_response',
                    'message' => $errorMessage . ': ' . substr($responseBody, 0, 200),
                    'raw_response' => $responseBody,
                    'api_response' => null,
                    'http_status_code' => $httpStatus,
                ];
            }

            // این بخش دیگر استفاده نمی‌شود چون خطاها در بخش بالا مدیریت می‌شوند
            // اما برای سازگاری نگه داشته شده است
        } catch (\Exception $e) {
            // بررسی اینکه آیا خطای timeout یا اتصال است
            $isTimeoutError = stripos($e->getMessage(), 'timeout') !== false || 
                             stripos($e->getMessage(), 'Failed to connect') !== false ||
                             stripos($e->getMessage(), 'Connection timed out') !== false ||
                             stripos($e->getMessage(), 'cURL error 28') !== false;
            
            if ($isTimeoutError) {
                Log::error('Melipayamak SendByBaseNumber2 Connection/Timeout Exception', [
                    'to' => $to,
                    'bodyId' => $bodyId,
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                ]);

                // اگر خطای timeout است، به REST API قدیمی fallback می‌کنیم
                Log::warning('Melipayamak Console API timeout, falling back to REST API', [
                    'console_error' => $e->getMessage(),
                ]);
                
                // تلاش با REST API قدیمی
                $restResult = $this->sendByBaseNumberRest($to, $bodyId, $variables, $from);
                if ($restResult['success']) {
                    Log::info('Melipayamak SendByBaseNumber2 - REST API succeeded after Console timeout', [
                        'to' => $to,
                        'bodyId' => $bodyId,
                    ]);
                    return $restResult;
                }
                
                // اگر REST API هم کار نکرد، خطای timeout را برمی‌گردانیم
                return [
                    'success' => false,
                    'message' => 'خطا در اتصال به سرویس: ' . $e->getMessage(),
                    'raw_response' => $e->getMessage(),
                    'http_status_code' => null,
                    'response_code' => 'connection_error',
                ];
            }
            Log::error('Melipayamak SendByBaseNumber2 Exception', [
                'to' => $to,
                'bodyId' => $bodyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'خطا در اتصال به سرویس: ' . $e->getMessage(),
                'raw_response' => $e->getMessage(),
                'http_status_code' => null,
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
