<?php

namespace App\Livewire\Patterns;

use Livewire\Component;
use App\Models\Pattern;
use App\Models\Blacklist;
use App\Models\Report;
use App\Models\Category;
use App\Models\PatternVariable;
use App\Models\PatternPatternVariable;
use App\Services\MelipayamakService;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 20;
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = 'approved'; // پیش‌فرض: الگوهای تایید شده از API
    
    // Modal states
    public $showModal = false;
    public $isEditing = false;
    public $editingId = null;
    
    // Form fields
    public $title = '';
    public $text = '';
    public $pattern_code = '';
    public $blacklist_id = '1'; // مقدار پیش‌فرض
    public $status = 'pending';
    public $rejection_reason = '';
    public $is_active = true;
    
    // API Response Modal
    public $showApiResponseModal = false;
    public $apiResponseData = null;
    
    // Sync from API
    public $syncing = false;
    
    // View Raw API Response
    public $showRawApiResponseModal = false;
    public $rawApiResponseData = null;
    
    // نمایش پاسخ API بعد از همگام‌سازی
    public $showSyncResponseModal = false;
    public $syncResponseData = null;

    public function mount()
    {
        // اگر از route /patterns/create آمده‌ایم، مودال ایجاد را باز می‌کنیم
        if (request()->is('patterns/create*')) {
            $this->openCreateModal();
        }
    }

    public function syncFromApi()
    {
        try {
            // دریافت API Key از دیتابیس با ساختار JSON جدید
            $apiKeyRecord = \App\Models\ApiKey::where('key_name', 'main_api')
                ->where('is_active', true)
                ->first();
            
            $actualUsername = null;
            $actualApiKey = null;
            $apiKeyFromDb = false;
            
            if ($apiKeyRecord) {
                $keyData = json_decode($apiKeyRecord->key_value, true);
                
                if ($keyData && isset($keyData['username']) && isset($keyData['api_key'])) {
                    $actualUsername = $keyData['username'];
                    $actualApiKey = $keyData['api_key'];
                    $apiKeyFromDb = true;
                }
            }
            
            // اگر در دیتابیس نبود، از config می‌خوانیم
            if (empty($actualUsername) || empty($actualApiKey)) {
                $actualUsername = config('services.melipayamak.username');
                $actualApiKey = config('services.melipayamak.api_key') 
                    ?: config('services.melipayamak.password');
                $apiKeyFromDb = false;
            }
            
            // تعیین منبع API Key
            $apiKeySource = $apiKeyFromDb 
                ? 'دیتابیس (main_api)' 
                : 'فایل کانفیگ';
            
            // استفاده از curl مستقیم به جای HTTP Client
            $url = "https://api.payamak-panel.com/post/SharedService.asmx/GetSharedServiceBody";
            $postData = http_build_query([
                'username' => $actualUsername,
                'password' => $actualApiKey,
            ]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: ' . strlen($postData)
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                
                // پردازش پاسخ XML
                $patterns = [];
                try {
                    $xml = simplexml_load_string($responseBody);
                    if ($xml && isset($xml->ShareServiceBody)) {
                        foreach ($xml->ShareServiceBody as $pattern) {
                            $patterns[] = [
                                'id' => (string)$pattern->BodyID,
                                'name' => (string)$pattern->Title,
                                'body' => (string)$pattern->Body,
                                'body_status' => (string)$pattern->BodyStatus,
                                'description' => (string)$pattern->Description,
                                'insert_date' => (string)$pattern->InsertDate
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // اگر XML parse نشد، سعی می‌کنیم به صورت رشته پردازش کنیم
                    \Log::error('XML Parse Error', ['error' => $e->getMessage()]);
                }
                
                $patternsCount = count($patterns);
                
                // آماده‌سازی داده‌های نمایشی با اطلاعات کامل
                $this->syncResponseData = [
                    'success' => true,
                    'title' => 'همگام‌سازی الگوها',
                    'message' => "الگوها با موفقیت از API ملی پیامک دریافت و در دیتابیس ذخیره شدند ({$patternsCount} الگو)",
                    'username' => $actualUsername ?: '-',
                    'api_key' => $actualApiKey ? substr($actualApiKey, 0, 10) . '...' : '-',
                    'api_key_source' => $apiKeySource,
                    'api_key_from_db' => $apiKeyFromDb,
                    'patterns_count' => $patternsCount,
                    'http_status_code' => $httpCode,
                    'patterns' => $patterns,
                    'raw_response' => $responseBody
                ];
                
                // ذخیره الگوها در دیتابیس
                $this->savePatternsToDatabase($patterns);
                
            } else {
                throw new \Exception('خطا در تماس با API: HTTP ' . $httpCode);
            }
            
            $this->showSyncResponseModal = true;
            
        } catch (\Exception $e) {
            $this->syncResponseData = [
                'success' => false,
                'title' => 'همگام‌سازی الگوها',
                'message' => 'خطا در همگام‌سازی: ' . $e->getMessage(),
                'username' => $actualUsername ?? '-',
                'api_key' => $actualApiKey ? substr($actualApiKey, 0, 10) . '...' : '-',
                'api_key_source' => $apiKeyFromDb ? 'دیتابیس (main_api)' : 'فایل کانفیگ',
                'api_key_from_db' => $apiKeyFromDb ?? false,
                'patterns_count' => 0,
                'http_status_code' => 500,
                'raw_response' => $e->getMessage()
            ];
            $this->showSyncResponseModal = true;
        }
    }

    /**
     * تبدیل body_status به status مناسب
     */
    private function getStatusFromBodyStatus($bodyStatus)
    {
        // بر اساس توضیح شما:
        // 1: تایید شده
        // بقیه: تایید نشده (در انتظار تایید)
        switch ($bodyStatus) {
            case 1:
                return 'approved';
            case 2:
            case 3:
            case 4:
            case 5:
            default:
                return 'pending'; // به صورت پیش‌فرض در انتظار تایید
        }
    }

    /**
     * تبدیل body_status به is_active مناسب
     */
    private function getActiveFromBodyStatus($bodyStatus)
    {
        // فقط body_status = 1 فعال هست، بقیه غیرفعال
        return $bodyStatus == 1;
    }

    public function closeSyncResponseModal()
    {
        $this->showSyncResponseModal = false;
        $this->syncResponseData = null;
    }

    /**
     * ذخیره الگوهای دریافت شده از API در دیتابیس
     */
    private function savePatternsToDatabase($patterns)
    {
        try {
            $savedCount = 0;
            $updatedCount = 0;
            
            foreach ($patterns as $patternData) {
                // بررسی اینکه آیا الگو قبلاً وجود دارد
                $existingPattern = Pattern::where('pattern_code', $patternData['id'])->first();
                
                if ($existingPattern) {
                    // به‌روزرسانی الگوی موجود
                    $existingPattern->update([
                        'title' => $patternData['name'],
                        'text' => $patternData['body'],
                        'pattern_code' => $patternData['id'],
                        'status' => $this->getStatusFromBodyStatus($patternData['body_status'] ?? 5),
                        'is_active' => $this->getActiveFromBodyStatus($patternData['body_status'] ?? 5),
                        'api_response' => json_encode($patternData),
                        'http_status_code' => 200,
                    ]);
                    $updatedCount++;
                } else {
                    // ایجاد الگوی جدید
                    Pattern::create([
                        'title' => $patternData['name'],
                        'text' => $patternData['body'],
                        'pattern_code' => $patternData['id'],
                        'blacklist_id' => 1, // مقدار پیش‌فرض
                        'status' => $this->getStatusFromBodyStatus($patternData['body_status'] ?? 5),
                        'is_active' => $this->getActiveFromBodyStatus($patternData['body_status'] ?? 5),
                        'api_response' => json_encode($patternData),
                        'http_status_code' => 200,
                    ]);
                    $savedCount++;
                }
            }
            
            // ریست کردن pagination برای نمایش الگوهای جدید
            $this->resetPage();
            $this->reset(['search', 'statusFilter']); // ریست فیلترها هم
            
            \Log::info('Patterns saved to database', [
                'total_patterns' => count($patterns),
                'saved_count' => $savedCount,
                'updated_count' => $updatedCount,
            ]);
            
            // نمایش پیام موفقیت
            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => "{$savedCount} الگوی جدید ایجاد شد و {$updatedCount} الگو به‌روزرسانی شد."
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error saving patterns to database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e; // دوباره خطا رو نمایش بده
        }
    }

    public function viewRawApiResponse()
    {
        try {
            // دریافت API Key از دیتابیس با ساختار JSON جدید
            $apiKeyRecord = \App\Models\ApiKey::where('key_name', 'main_api')
                ->where('is_active', true)
                ->first();
            
            $actualUsername = null;
            $actualApiKey = null;
            $apiKeyFromDb = false;
            
            if ($apiKeyRecord) {
                $keyData = json_decode($apiKeyRecord->key_value, true);
                
                if ($keyData && isset($keyData['username']) && isset($keyData['api_key'])) {
                    $actualUsername = $keyData['username'];
                    $actualApiKey = $keyData['api_key'];
                    $apiKeyFromDb = true;
                }
            }
            
            // اگر در دیتابیس نبود، از config می‌خوانیم
            if (empty($actualUsername) || empty($actualApiKey)) {
                $actualUsername = config('services.melipayamak.username');
                $actualApiKey = config('services.melipayamak.api_key') 
                    ?: config('services.melipayamak.password');
                $apiKeyFromDb = false;
            }
            
            // استفاده از REST API ملی پیامک
            $url = "https://api.payamak-panel.com/post/SharedService.asmx/GetSharedServiceBody";
            $data = [
                'username' => $actualUsername,
                'password' => $actualApiKey,
            ];
            
            $response = \Http::post($url, $data);
            
            if ($response->successful()) {
                $this->rawApiResponseData = [
                    'success' => true,
                    'title' => 'پاسخ خام API - GetSharedServiceBody',
                    'http_status_code' => $response->status(),
                    'patterns' => [],
                    'raw_response' => $response->body()
                ];
                
                // تلاش برای پردازش الگوها برای نمایش
                try {
                    $xml = simplexml_load_string($response->body());
                    if ($xml && isset($xml->ShareServiceBody)) {
                        foreach ($xml->ShareServiceBody as $pattern) {
                            $this->rawApiResponseData['patterns'][] = [
                                'id' => (string)$pattern->BodyID,
                                'name' => (string)$pattern->Title,
                                'body' => (string)$pattern->Body,
                                'body_status' => (string)$pattern->BodyStatus,
                                'description' => (string)$pattern->Description,
                                'insert_date' => (string)$pattern->InsertDate
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // XML parse error
                }
                
                $this->showRawApiResponseModal = true;
            } else {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'خطا در دریافت اطلاعات از API: ' . $response->status()
                ]);
            }
            
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا: ' . $e->getMessage()
            ]);
        }
    }

    public function closeRawApiResponseModal()
    {
        $this->showRawApiResponseModal = false;
        $this->rawApiResponseData = null;
    }

    public function render()
    {
        $query = Pattern::query();

        // جستجو
        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('text', 'like', '%' . $this->search . '%')
                  ->orWhere('pattern_code', 'like', '%' . $this->search . '%');
            });
        }

        // فیلتر وضعیت
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // مرتب‌سازی
        $query->orderBy($this->sortBy, $this->sortDirection);

        $patterns = $query->paginate($this->perPage);

        return view('livewire.patterns.index', [
            'patterns' => $patterns
        ]);
    }
}
