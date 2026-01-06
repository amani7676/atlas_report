<?php

namespace App\Livewire\Patterns;

use Livewire\Component;
use App\Models\Pattern;
use App\Models\Blacklist;
use App\Models\Report;
use App\Models\Category;
use App\Models\PatternVariable;
use App\Services\MelipayamakService;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 20;
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = 'approved'; // پیش‌فرض: فقط الگوهای تایید شده
    
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
    
    // Variable selection for pattern text
    public $selectedVariables = [];
    public $availableVariables = [];
    public $variableCounter = 0;

    protected $rules = [
        'title' => 'required|string|max:255',
        'text' => 'required|string',
        'pattern_code' => 'nullable|string|max:255',
        'blacklist_id' => 'required|string',
        'status' => 'required|in:pending,approved,rejected',
        'rejection_reason' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        // اگر از route /patterns/create آمده‌ایم، مودال ایجاد را باز می‌کنیم
        if (request()->is('patterns/create*')) {
            $this->openCreateModal();
        }
        
        // بارگذاری متغیرهای موجود
        $this->loadAvailableVariables();
    }
    
    public function loadAvailableVariables()
    {
        // بارگذاری متغیرها از دیتابیس
        $variables = PatternVariable::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
        
        $userVariables = [];
        $reportVariables = [];
        $generalVariables = [];
        
        foreach ($variables as $variable) {
            $varData = [
                'key' => $variable->table_field,
                'label' => $variable->title,
                'type' => $variable->variable_type,
                'code' => $variable->code,
                'table_name' => $variable->table_name,
                'description' => $variable->description,
            ];
            
            if ($variable->variable_type === 'user') {
                $userVariables[] = $varData;
            } elseif ($variable->variable_type === 'report') {
                $reportVariables[] = $varData;
            } else {
                $generalVariables[] = $varData;
            }
        }
        
        $this->availableVariables = [
            'user' => $userVariables,
            'report' => $reportVariables,
            'general' => $generalVariables,
        ];
    }
    
    public function insertVariable($variableKey, $variableType)
    {
        // پیدا کردن متغیر از دیتابیس
        $variable = PatternVariable::where('table_field', $variableKey)
            ->where('variable_type', $variableType)
            ->where('is_active', true)
            ->first();
        
        if ($variable) {
            // استفاده از کد متغیر از دیتابیس (مثل {0}, {1})
            $variablePlaceholder = $variable->code;
            
            // اضافه کردن به متن (در مکان فعلی cursor یا انتهای متن)
            $this->text .= $variablePlaceholder;
            
            // ذخیره اطلاعات متغیر
            // استخراج عدد از کد (مثل {0} -> 0)
            preg_match('/\{(\d+)\}/', $variable->code, $matches);
            $index = isset($matches[1]) ? (int)$matches[1] : $this->variableCounter;
            
            $this->selectedVariables[] = [
                'index' => $index,
                'key' => $variable->table_field,
                'label' => $variable->title,
                'type' => $variable->variable_type,
                'code' => $variable->code,
                'table_name' => $variable->table_name,
            ];
            
            // به‌روزرسانی شمارنده
            if ($index >= $this->variableCounter) {
                $this->variableCounter = $index + 1;
            }
        } else {
            // اگر متغیر در دیتابیس پیدا نشد، از روش قدیمی استفاده می‌کنیم
            $variable = null;
            foreach ($this->availableVariables[$variableType] ?? [] as $var) {
                if ($var['key'] === $variableKey) {
                    $variable = $var;
                    break;
                }
            }
            
            if ($variable) {
                $variablePlaceholder = '{' . $this->variableCounter . '}';
                $this->text .= $variablePlaceholder;
                
                $this->selectedVariables[] = [
                    'index' => $this->variableCounter,
                    'key' => $variableKey,
                    'label' => $variable['label'],
                    'type' => $variableType,
                ];
                
                $this->variableCounter++;
            }
        }
    }
    
    public function removeVariable($index)
    {
        // پیدا کردن متغیر با این index
        $variableToRemove = null;
        foreach ($this->selectedVariables as $key => $var) {
            if ($var['index'] == $index) {
                $variableToRemove = $var;
                unset($this->selectedVariables[$key]);
                break;
            }
        }
        
        if ($variableToRemove) {
            // حذف کد متغیر از متن (مثل {0}, {1})
            $codeToRemove = $variableToRemove['code'] ?? '{' . $index . '}';
            $this->text = str_replace($codeToRemove, '', $this->text);
            
            // بازسازی آرایه
            $this->selectedVariables = array_values($this->selectedVariables);
        }
        
        // بازسازی شماره‌گذاری (اختیاری - اگر بخواهیم از صفر شروع کنیم)
        // $this->rebuildVariableIndices();
    }
    
    public function rebuildVariableIndices()
    {
        // بازسازی شماره‌گذاری متغیرها از صفر
        $newVariables = [];
        $newCounter = 0;
        $oldToNew = [];
        
        foreach ($this->selectedVariables as $var) {
            $oldIndex = $var['index'];
            $oldToNew[$oldIndex] = $newCounter;
            $newVariables[] = [
                'index' => $newCounter,
                'key' => $var['key'],
                'label' => $var['label'],
                'type' => $var['type'],
            ];
            $newCounter++;
        }
        
        $this->selectedVariables = $newVariables;
        $this->variableCounter = $newCounter;
        
        // جایگزینی در متن
        foreach ($oldToNew as $oldIndex => $newIndex) {
            $this->text = str_replace('{' . $oldIndex . '}', '{' . $newIndex . '}', $this->text);
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $pattern = Pattern::findOrFail($id);
        $this->editingId = $id;
        $this->title = $pattern->title;
        $this->text = $pattern->text;
        $this->pattern_code = $pattern->pattern_code ?? '';
        $this->blacklist_id = $pattern->blacklist_id;
        $this->status = $pattern->status;
        $this->rejection_reason = $pattern->rejection_reason ?? '';
        $this->is_active = $pattern->is_active;
        $this->isEditing = true;
        $this->showModal = true;
        
        // استخراج متغیرها از متن
        $this->extractVariablesFromText();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->title = '';
        $this->text = '';
        $this->pattern_code = '';
        $this->blacklist_id = '1'; // مقدار پیش‌فرض
        $this->status = 'pending';
        $this->rejection_reason = '';
        $this->is_active = true;
        $this->editingId = null;
        $this->selectedVariables = [];
        $this->variableCounter = 0;
        $this->resetValidation();
    }
    
    public function extractVariablesFromText()
    {
        // استخراج متغیرها از متن برای نمایش در ویرایش
        preg_match_all('/\{(\d+)\}/', $this->text, $matches);
        if (!empty($matches[1])) {
            $codes = $matches[0]; // {0}, {1}, {2}
            $indices = array_map('intval', $matches[1]);
            $maxIndex = max($indices);
            $this->variableCounter = $maxIndex + 1;
            
            // تلاش برای پیدا کردن متغیرها از دیتابیس
            $this->selectedVariables = [];
            foreach ($codes as $code) {
                $variable = PatternVariable::where('code', $code)->first();
                if ($variable) {
                    preg_match('/\{(\d+)\}/', $code, $codeMatches);
                    $index = isset($codeMatches[1]) ? (int)$codeMatches[1] : 0;
                    
                    $this->selectedVariables[] = [
                        'index' => $index,
                        'key' => $variable->table_field,
                        'label' => $variable->title,
                        'type' => $variable->variable_type,
                        'code' => $variable->code,
                        'table_name' => $variable->table_name,
                    ];
                } else {
                    // اگر متغیر در دیتابیس پیدا نشد
                    preg_match('/\{(\d+)\}/', $code, $codeMatches);
                    $index = isset($codeMatches[1]) ? (int)$codeMatches[1] : 0;
                    
                    $this->selectedVariables[] = [
                        'index' => $index,
                        'key' => 'unknown',
                        'label' => 'متغیر ' . $code,
                        'type' => 'unknown',
                        'code' => $code,
                    ];
                }
            }
        }
    }

    public function createPattern()
    {
        $this->validate();

        try {
            // ایجاد الگو در API
            $melipayamakService = new MelipayamakService();
            $result = $melipayamakService->sharedServiceBodyAdd(
                $this->title,
                $this->text,
                (int)$this->blacklist_id
            );

            if ($result['success']) {
                // ذخیره در دیتابیس
                $pattern = Pattern::create([
                    'title' => $this->title,
                    'text' => $this->text,
                    'pattern_code' => $result['pattern_code'] ?? $result['body_id'] ?? null,
                    'blacklist_id' => $this->blacklist_id,
                    'status' => 'pending', // بعد از تایید در پنل ملی پیامک تغییر می‌کند
                    'rejection_reason' => $this->rejection_reason ?: null,
                    'is_active' => $this->is_active,
                    'api_response' => $result['raw_response'],
                    'http_status_code' => $result['http_status_code'],
                ]);

                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'الگو با موفقیت در API ایجاد شد. کد الگو: ' . ($result['pattern_code'] ?? $result['body_id'] ?? '-')
                ]);

                $this->closeModal();
            } else {
                // ذخیره با خطا (برای بررسی بعدی)
                $pattern = Pattern::create([
                    'title' => $this->title,
                    'text' => $this->text,
                    'pattern_code' => null,
                    'blacklist_id' => $this->blacklist_id,
                    'status' => 'pending',
                    'rejection_reason' => $this->rejection_reason ?: null,
                    'is_active' => false,
                    'api_response' => $result['raw_response'],
                    'http_status_code' => $result['http_status_code'],
                ]);

                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => $result['message'] ?? 'خطا در ایجاد الگو در API'
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ایجاد الگو: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePattern()
    {
        $this->validate();

        try {
            $pattern = Pattern::findOrFail($this->editingId);
            
            // اگر pattern_code وجود دارد، در API هم ویرایش می‌کنیم
            if ($pattern->pattern_code) {
                $melipayamakService = new MelipayamakService();
                $result = $melipayamakService->sharedServiceBodyEdit(
                    (int)$pattern->pattern_code,
                    $this->title,
                    $this->text,
                    (int)$this->blacklist_id
                );

                if ($result['success']) {
                    // به‌روزرسانی در دیتابیس
                    // بعد از ویرایش موفق در API، وضعیت به pending تغییر می‌کند
                    // چون باید دوباره توسط مدیر سامانه تأیید شود
                    $pattern->update([
                        'title' => $this->title,
                        'text' => $this->text,
                        'blacklist_id' => $this->blacklist_id,
                        'status' => 'pending', // بعد از ویرایش، باید دوباره تأیید شود
                        'rejection_reason' => null, // دلیل رد قبلی پاک می‌شود
                        'is_active' => $this->is_active,
                        'api_response' => $result['raw_response'] ?? null,
                        'http_status_code' => $result['http_status_code'] ?? null,
                    ]);

                    // نمایش پاسخ API در مودال
                    $this->apiResponseData = [
                        'success' => true,
                        'message' => 'الگو با موفقیت در سامانه ملی پیامک ویرایش شد و برای تأیید ارسال شد.',
                        'api_response' => $result['raw_response'] ?? null,
                        'http_status_code' => $result['http_status_code'] ?? null,
                        'status' => 'pending',
                        'status_message' => 'در انتظار تأیید مدیر سامانه',
                    ];
                    $this->showApiResponseModal = true;

                    $this->dispatch('showAlert', [
                        'type' => 'success',
                        'title' => 'موفقیت!',
                        'text' => 'الگو با موفقیت در سامانه ملی پیامک ویرایش شد و برای تأیید ارسال شد. وضعیت: در انتظار تأیید'
                    ]);
                } else {
                    // اگر خطا در API بود، فقط در دیتابیس به‌روزرسانی می‌کنیم
                    $pattern->update([
                        'title' => $this->title,
                        'text' => $this->text,
                        'blacklist_id' => $this->blacklist_id,
                        'status' => $this->status,
                        'rejection_reason' => $this->rejection_reason ?: null,
                        'is_active' => $this->is_active,
                        'api_response' => $result['raw_response'] ?? null,
                        'http_status_code' => $result['http_status_code'] ?? null,
                    ]);

                    // نمایش پاسخ خطا در مودال
                    $this->apiResponseData = [
                        'success' => false,
                        'message' => $result['message'] ?? 'خطای نامشخص در API',
                        'api_response' => $result['raw_response'] ?? null,
                        'http_status_code' => $result['http_status_code'] ?? null,
                    ];
                    $this->showApiResponseModal = true;

                    $this->dispatch('showAlert', [
                        'type' => 'warning',
                        'title' => 'هشدار!',
                        'text' => 'الگو در دیتابیس به‌روزرسانی شد اما خطا در API: ' . ($result['message'] ?? 'خطای نامشخص')
                    ]);
                }
            } else {
                // اگر pattern_code نداریم، فقط در دیتابیس به‌روزرسانی می‌کنیم
                $pattern->update([
                    'title' => $this->title,
                    'text' => $this->text,
                    'blacklist_id' => $this->blacklist_id,
                    'status' => $this->status,
                    'rejection_reason' => $this->rejection_reason ?: null,
                    'is_active' => $this->is_active,
                ]);

                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'الگو در دیتابیس به‌روزرسانی شد. (کد الگو وجود ندارد - ابتدا باید الگو را در سامانه ملی پیامک ایجاد کنید)'
                ]);
            }

            $this->closeModal();
        } catch (\Exception $e) {
            \Log::error('Error updating pattern', [
                'pattern_id' => $this->editingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در به‌روزرسانی الگو: ' . $e->getMessage()
            ]);
        }
    }

    public function deletePattern($id)
    {
        try {
            $pattern = Pattern::findOrFail($id);
            $pattern->delete();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'الگو با موفقیت حذف شد.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در حذف الگو: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleActive($id)
    {
        try {
            $pattern = Pattern::findOrFail($id);
            $pattern->update(['is_active' => !$pattern->is_active]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'وضعیت الگو با موفقیت تغییر کرد.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در تغییر وضعیت: ' . $e->getMessage()
            ]);
        }
    }

    public function changeStatus($id, $status)
    {
        try {
            $pattern = Pattern::findOrFail($id);
            $pattern->update(['status' => $status]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'وضعیت الگو با موفقیت تغییر کرد.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در تغییر وضعیت: ' . $e->getMessage()
            ]);
        }
    }

    public function syncFromApi()
    {
        try {
            \Log::info('syncFromApi method called');
            $this->syncing = true;
            
            // دریافت API Key که واقعاً استفاده می‌شود (قبل از فراخوانی API)
            // استفاده مستقیم از Query Builder برای اطمینان از خواندن از جدول
            // بررسی تمام رکوردهای api key (حتی غیرفعال) برای دیباگ
            // استفاده از 'api key' (با فاصله) به جای 'api_key'
            $allApiKeyRecords = \App\Models\ApiKey::where('key_name', 'api key')->get();
            \Log::info('Patterns syncFromApi - All api key records in database', [
                'total_records' => $allApiKeyRecords->count(),
                'records' => $allApiKeyRecords->map(function($r) {
                    return [
                        'id' => $r->id,
                        'key_name' => $r->key_name,
                        'key_value' => $r->key_value,
                        'is_active' => $r->is_active,
                    ];
                })->toArray(),
            ]);
            
            $apiKeyRecord = \App\Models\ApiKey::where('key_name', 'api key')
                ->where('is_active', true)
                ->first();
            
            $actualApiKey = $apiKeyRecord ? $apiKeyRecord->key_value : null;
            $apiKeyFromDb = !empty($actualApiKey);
            
            // اگر در دیتابیس نبود، از config می‌خوانیم
            if (empty($actualApiKey)) {
                $actualApiKey = config('services.melipayamak.api_key') 
                    ?: config('services.melipayamak.password');
            }
            
            // دریافت Username که واقعاً استفاده می‌شود
            $usernameRecord = \App\Models\ApiKey::where('key_name', 'username')
                ->where('is_active', true)
                ->first();
            $actualUsername = $usernameRecord ? $usernameRecord->key_value : null;
            if (empty($actualUsername)) {
                $actualUsername = config('services.melipayamak.username');
            }
            
            // لاگ مقدار دقیقی که استفاده می‌شود
            \Log::info('Patterns syncFromApi - API Key that will be used', [
                'username' => $actualUsername,
                'api_key_from_database' => $apiKeyFromDb,
                'api_key_full_value' => $actualApiKey,
                'api_key_length' => strlen($actualApiKey ?? ''),
                'api_key_source' => $apiKeyFromDb ? 'database (api_keys table - key_name = "api key")' : 'config',
                'database_record' => $apiKeyRecord ? [
                    'id' => $apiKeyRecord->id,
                    'key_name' => $apiKeyRecord->key_name,
                    'is_active' => $apiKeyRecord->is_active,
                    'value_length' => strlen($apiKeyRecord->key_value),
                    'value_preview' => substr($apiKeyRecord->key_value, 0, 20) . '...',
                ] : 'not found',
            ]);
            
            // دریافت مقدار دقیق API Key که در MelipayamakService استفاده می‌شود
            // استفاده مستقیم از Query Builder برای اطمینان
            // استفاده از 'api key' (با فاصله) به جای 'api_key'
            $dbApiKeyRecord = \App\Models\ApiKey::where('key_name', 'api key')
                ->where('is_active', true)
                ->first();
            
            // این مقدار دقیقی است که در API استفاده می‌شود
            $actualApiKeyUsed = $dbApiKeyRecord ? $dbApiKeyRecord->key_value : null;
            
            // اگر در دیتابیس نبود، از config می‌خوانیم (مثل MelipayamakService)
            if (empty($actualApiKeyUsed)) {
                $actualApiKeyUsed = config('services.melipayamak.api_key') 
                    ?: config('services.melipayamak.password');
            }
            
            // دریافت Username
            $dbUsernameRecord = \App\Models\ApiKey::where('key_name', 'username')
                ->where('is_active', true)
                ->first();
            $actualUsernameUsed = $dbUsernameRecord ? $dbUsernameRecord->key_value : null;
            if (empty($actualUsernameUsed)) {
                $actualUsernameUsed = config('services.melipayamak.username');
            }
            
            // لاگ مقدار دقیقی که استفاده می‌شود
            \Log::info('Patterns syncFromApi - Actual values that will be used in API', [
                'username_used' => $actualUsernameUsed,
                'api_key_used' => $actualApiKeyUsed,
                'api_key_length' => strlen($actualApiKeyUsed ?? ''),
                'api_key_from_database' => !empty($dbApiKeyRecord),
                'database_record_id' => $dbApiKeyRecord ? $dbApiKeyRecord->id : null,
                'database_record_key_name' => $dbApiKeyRecord ? $dbApiKeyRecord->key_name : null,
                'database_record_value' => $dbApiKeyRecord ? $dbApiKeyRecord->key_value : null,
                'database_record_is_active' => $dbApiKeyRecord ? $dbApiKeyRecord->is_active : null,
            ]);
            
            $melipayamakService = new MelipayamakService();
            $result = $melipayamakService->getSharedServiceBody();

            \Log::info('syncFromApi API result', [
                'success' => $result['success'] ?? false,
                'patterns_count' => count($result['patterns'] ?? []),
            ]);
            
            // تعیین منبع API Key
            $apiKeySource = $apiKeyFromDb 
                ? 'database (جدول api_keys - key_name = "api key")' 
                : (!empty(config('services.melipayamak.api_key')) 
                    ? 'config (services.melipayamak.api_key)' 
                    : 'config (services.melipayamak.password)');
            
            // استفاده از مقادیر دقیقی که از جدول خوانده شده‌اند
            // این مقادیر همان مقادیری هستند که در API استفاده می‌شوند
            $this->syncResponseData = [
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? '',
                'patterns_count' => count($result['patterns'] ?? []),
                'patterns' => $result['patterns'] ?? [],
                'raw_response' => $result['raw_response'] ?? $result['api_response'] ?? '',
                'http_status_code' => $result['http_status_code'] ?? null,
                'username' => $actualUsernameUsed, // Username که از جدول خوانده شده
                'password' => $actualApiKeyUsed, // API Key که از جدول api_keys خوانده شده است
                'api_key_source' => $apiKeySource, // منبع API Key
                'api_key_length' => strlen($actualApiKeyUsed ?? ''), // طول API Key
            ];
            
            // لاگ نهایی
            \Log::info('Patterns syncFromApi - Final display values', [
                'display_username' => $actualUsernameUsed,
                'display_api_key' => $actualApiKeyUsed,
                'display_api_key_length' => strlen($actualApiKeyUsed ?? ''),
                'display_api_key_source' => $apiKeySource,
                'database_record_exists' => !empty($apiKeyRecord),
            ]);
            
            // لاگ نهایی
            \Log::info('Patterns syncFromApi - Final display values', [
                'display_username' => $actualUsernameUsed,
                'display_api_key' => $actualApiKeyUsed,
                'display_api_key_length' => strlen($actualApiKeyUsed ?? ''),
                'display_api_key_source' => $apiKeySource,
                'database_record_exists' => !empty($dbApiKeyRecord),
            ]);
            $this->showSyncResponseModal = true;

            if ($result['success']) {
                if (empty($result['patterns'])) {
                    $this->dispatch('showAlert', [
                        'type' => 'info',
                        'title' => 'اطلاعات',
                        'text' => 'هیچ الگویی در پنل ملی پیامک یافت نشد. لطفاً ابتدا یک الگو در پنل ایجاد کنید.'
                    ]);
                } else {
                    $syncedCount = 0;
                    $updatedCount = 0;
                    $errorCount = 0;
                    
                    \Log::info('Starting to sync patterns to database', [
                        'patterns_count' => count($result['patterns']),
                    ]);
                    
                    foreach ($result['patterns'] as $index => $apiPattern) {
                        try {
                            \Log::debug('Processing pattern', [
                                'index' => $index,
                                'pattern_data' => $apiPattern,
                            ]);
                            
                            // بررسی اینکه آیا الگو با این pattern_code وجود دارد
                            $patternCode = $apiPattern['pattern_code'] ?? null;
                            
                            if ($patternCode) {
                                $existingPattern = Pattern::where('pattern_code', $patternCode)->first();
                            } else {
                                // اگر pattern_code نداریم، بر اساس title و text بررسی می‌کنیم
                                $existingPattern = Pattern::where('title', $apiPattern['title'] ?? '')
                                    ->where('text', $apiPattern['text'] ?? '')
                                    ->first();
                            }
                            
                            // ساخت پاسخ API برای این الگو (فقط این الگو)
                            $patternApiResponse = json_encode([
                                'BodyID' => $patternCode,
                                'Title' => $apiPattern['title'] ?? '',
                                'Body' => $apiPattern['text'] ?? '',
                                'BodyStatus' => $apiPattern['body_status'] ?? '1',
                                'InsertDate' => $apiPattern['insert_date'] ?? null,
                                'Description' => $apiPattern['description'] ?? '',
                            ], JSON_UNESCAPED_UNICODE);
                            
                            // آماده‌سازی داده‌ها برای ذخیره در جدول patterns
                            $patternData = [
                                'title' => $apiPattern['title'] ?? 'بدون عنوان',
                                'text' => $apiPattern['text'] ?? '',
                                'pattern_code' => $patternCode,
                                'blacklist_id' => $apiPattern['blacklist_id'] ?? '1',
                                'status' => $apiPattern['status'] ?? 'approved',
                                'api_response' => $patternApiResponse,
                                'http_status_code' => $result['http_status_code'],
                            ];
                            
                            if ($existingPattern) {
                                // به‌روزرسانی الگوی موجود در جدول patterns
                                $existingPattern->update($patternData);
                                \Log::info('Pattern updated in database', [
                                    'pattern_id' => $existingPattern->id,
                                    'pattern_code' => $patternCode,
                                    'title' => $patternData['title'],
                                ]);
                                $updatedCount++;
                            } else {
                                // ایجاد الگوی جدید در جدول patterns
                                $patternData['is_active'] = true;
                                $newPattern = Pattern::create($patternData);
                                \Log::info('Pattern created in database', [
                                    'pattern_id' => $newPattern->id,
                                    'pattern_code' => $patternCode,
                                    'title' => $patternData['title'],
                                    'text_preview' => substr($patternData['text'], 0, 50),
                                ]);
                                $syncedCount++;
                            }
                        } catch (\Exception $e) {
                            $errorCount++;
                            \Log::error('Error syncing pattern to database', [
                                'index' => $index,
                                'pattern' => $apiPattern,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }
                    
                    \Log::info('Patterns sync completed', [
                        'synced' => $syncedCount,
                        'updated' => $updatedCount,
                        'errors' => $errorCount,
                    ]);

                    $message = "همگام‌سازی با موفقیت انجام شد. {$syncedCount} الگوی جدید اضافه شد و {$updatedCount} الگو به‌روزرسانی شد.";
                    if ($errorCount > 0) {
                        $message .= " ({$errorCount} خطا در پردازش)";
                    }

                    // Reset pagination and filters to show all patterns from database
                    $this->resetPage();
                    $this->reset(['search', 'statusFilter']);
                    
                    // نمایش پیام موفقیت
                    $this->dispatch('showAlert', [
                        'type' => 'success',
                        'title' => 'موفقیت!',
                        'text' => $message
                    ]);
                    
                    // Livewire به صورت خودکار کامپوننت را رندر می‌کند و لیست الگوها از جدول patterns نمایش داده می‌شود
                }
            } else {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => $result['message'] ?? 'خطا در دریافت الگوها از API. لطفاً پاسخ API را بررسی کنید.'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('syncFromApi Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در همگام‌سازی: ' . $e->getMessage()
            ]);
        } finally {
            $this->syncing = false;
        }
    }

    public function viewRawApiResponse()
    {
        try {
            // دریافت API Key که واقعاً استفاده می‌شود (از جدول api_keys)
            $apiKeyRecord = \App\Models\ApiKey::where('key_name', 'api_key')
                ->where('is_active', true)
                ->first();
            
            $apiKey = $apiKeyRecord ? $apiKeyRecord->key_value : null;
            
            // اگر در دیتابیس نبود، از config می‌خوانیم
            if (empty($apiKey)) {
                $apiKey = config('services.melipayamak.api_key') 
                    ?: config('services.melipayamak.password');
            }
            
            // دریافت Username
            $usernameRecord = \App\Models\ApiKey::where('key_name', 'username')
                ->where('is_active', true)
                ->first();
            $username = $usernameRecord ? $usernameRecord->key_value : null;
            if (empty($username)) {
                $username = config('services.melipayamak.username');
            }
            
            // تعیین منبع API Key
            $apiKeySource = !empty($apiKeyRecord) 
                ? 'database (جدول api_keys - key_name = "api_key")' 
                : (!empty(config('services.melipayamak.api_key')) 
                    ? 'config (services.melipayamak.api_key)' 
                    : 'config (services.melipayamak.password)');
            
            $melipayamakService = new MelipayamakService();
            $result = $melipayamakService->getSharedServiceBody();

            // لاگ برای دیباگ
            \Log::info('Patterns viewRawApiResponse - API Key info', [
                'username' => $username,
                'api_key' => $apiKey,
                'api_key_length' => strlen($apiKey ?? ''),
                'api_key_source' => $apiKeySource,
            ]);
            
            $this->rawApiResponseData = [
                'success' => $result['success'],
                'message' => $result['message'] ?? '',
                'patterns_count' => count($result['patterns'] ?? []),
                'raw_response' => $result['raw_response'] ?? '',
                'http_status_code' => $result['http_status_code'] ?? null,
                'patterns' => $result['patterns'] ?? [],
                'username' => $username ?? '-', // Username که استفاده شده
                'password' => $apiKey ?? '-', // API Key که استفاده شده است
                'api_key_source' => $apiKeySource ?? 'نامشخص', // منبع API Key
                'api_key_length' => strlen($apiKey ?? ''), // طول API Key
            ];
            
            $this->showRawApiResponseModal = true;
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در دریافت پاسخ API: ' . $e->getMessage()
            ]);
        }
    }

    public function closeRawApiResponseModal()
    {
        $this->showRawApiResponseModal = false;
        $this->rawApiResponseData = null;
    }
    
    public function closeSyncResponseModal()
    {
        $this->showSyncResponseModal = false;
        $this->syncResponseData = null;
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function showApiResponse($id)
    {
        $pattern = Pattern::findOrFail($id);
        
        // اگر api_response وجود دارد، آن را پارس می‌کنیم
        $parsedResponse = null;
        if ($pattern->api_response) {
            $parsedResponse = json_decode($pattern->api_response, true);
        }
        
        $this->apiResponseData = [
            'title' => $pattern->title,
            'pattern_code' => $pattern->pattern_code,
            'api_response' => $pattern->api_response,
            'parsed_response' => $parsedResponse,
            'http_status_code' => $pattern->http_status_code,
            'created_at' => $pattern->created_at,
        ];
        $this->showApiResponseModal = true;
    }

    public function closeApiResponseModal()
    {
        $this->showApiResponseModal = false;
        $this->apiResponseData = null;
    }

    public function getActiveBlacklistsProperty()
    {
        return Blacklist::where('is_active', true)
            ->whereNotNull('blacklist_id')
            ->orderBy('title')
            ->get();
    }

    public function getPatternsQueryProperty()
    {
        return Pattern::withCount(['reports' => function ($query) {
            $query->where('report_pattern.is_active', true);
        }])
        ->when($this->search, function ($query) {
            $query->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('text', 'like', '%' . $this->search . '%')
                ->orWhere('pattern_code', 'like', '%' . $this->search . '%');
        })
        ->when($this->statusFilter, function ($query) {
            $query->where('status', $this->statusFilter);
        })
        ->orderBy($this->sortBy, $this->sortDirection);
    }
    
    public function getReportsProperty()
    {
        return Report::with('category')->orderBy('title')->get();
    }
    
    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get();
    }

    public function render()
    {
        $patterns = $this->patternsQuery->paginate($this->perPage);
        $activeBlacklists = $this->activeBlacklists;
        $reports = $this->reports;
        $categories = $this->categories;

        return view('livewire.patterns.index', compact('patterns', 'activeBlacklists', 'reports', 'categories'));
    }
}
