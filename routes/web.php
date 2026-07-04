<?php



use App\Livewire\Dashboard;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Reports\Create as ReportsCreate;
use App\Livewire\Reports\Edit as ReportsEdit;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Categories\Create as CategoriesCreate;
use App\Livewire\Categories\Edit as CategoriesEdit;
use App\Livewire\Residents\ResidentReports;
use App\Livewire\Residents\Units;
use App\Livewire\Residents\ExpiredToday;
use App\Livewire\Admin\CardThresholds;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::get('/register', Register::class)->name('register')->middleware('guest');
Route::post('/logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Redirect root to login if not authenticated, otherwise to dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', Dashboard::class)->name('dashboard')->middleware('auth');
Route::get('/reports', ReportsIndex::class)->name('reports.index')->middleware('auth');
Route::get('/reports/create', ReportsCreate::class)->name('reports.create')->middleware('auth');
Route::get('/reports/edit/{id}', ReportsEdit::class)->name('reports.edit')->middleware('auth');
Route::get('/categories', CategoriesIndex::class)->name('categories.index')->middleware('auth');
Route::get('/categories/create', CategoriesCreate::class)->name('categories.create')->middleware('auth');
Route::get('/categories/edit/{id}', CategoriesEdit::class)->name('categories.edit')->middleware('auth');

Route::get('/residents', Units::class)->name('residents.units')->middleware('auth');
Route::get('/resident-reports', ResidentReports::class)->name('residents.reports')->middleware('auth');
Route::get('/resident-reports/notifications', \App\Livewire\Residents\NotificationReports::class)->name('residents.notification-reports')->middleware('auth');
Route::get('/residents/expired-today', ExpiredToday::class)->name('residents.expired-today')->middleware('auth');
Route::get('/residents/group-sms', \App\Livewire\Residents\GroupSms::class)->name('residents.group-sms')->middleware('auth');
Route::get('/reports/violations', \App\Livewire\Reports\Violations::class)->name('reports.violations')->middleware('auth');
Route::get('/blacklists', \App\Livewire\Blacklists\Index::class)->name('blacklists.index')->middleware('auth');
Route::get('/patterns', \App\Livewire\Patterns\Index::class)->name('patterns.index')->middleware('auth');
Route::get('/patterns/create', \App\Livewire\Patterns\Index::class)->name('patterns.create')->middleware('auth');
Route::get('/variables', \App\Livewire\Variables\Index::class)->name('variables.index')->middleware('auth');
Route::get('/variables/create', \App\Livewire\Variables\Index::class)->name('variables.create')->middleware('auth');
Route::get('/sender-numbers', \App\Livewire\Admin\SenderNumbers::class)->name('sender-numbers.index')->middleware('auth');
Route::get('/api-keys', \App\Livewire\Admin\ApiKeyManager::class)->name('api-keys.index')->middleware('auth');
Route::get('/api-manager', \App\Livewire\Admin\ApiManager::class)->name('api-manager.index')->middleware('auth');
Route::get('/constants', \App\Livewire\Constants\Index::class)->name('constants.index')->middleware('auth');
Route::get('/table-names', \App\Livewire\TableNames\Index::class)->name('table-names.index')->middleware('auth');
Route::get('/settings', \App\Livewire\Settings\Index::class)->name('settings.index')->middleware('auth');
Route::get('/sms/sent', \App\Livewire\Sms\SentMessages::class)->name('sms.sent')->middleware('auth');
Route::get('/sms/api-messages', \App\Livewire\Sms\ApiMessages::class)->name('sms.api-messages')->middleware('auth');


// Test endpoint
Route::post('/test-sync', function () {
    return response()->json(['success' => true, 'message' => 'Test endpoint works!']);
});

// Update API URL endpoint
Route::get('/update-api-url', function () {
    try {
        $settings = \App\Models\Settings::getSettings();
        $settings->api_url = 'http://127.0.0.1:8000/api/residents';
        $settings->save();
        
        return response()->json([
            'success' => true,
            'message' => 'API URL updated to: ' . $settings->api_url
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
});

// Simple sync endpoint - using settings API URL
Route::post('/sync-data', function () {
    try {
        // \Log::info('=== Starting sync using settings API ===');
        
        // 1. پاک کردن کل جدول
        $deletedCount = \App\Models\Resident::count();
        \App\Models\Resident::query()->delete();
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE residents AUTO_INCREMENT = 1');
        // \Log::info("Deleted {$deletedCount} residents from database");
        
        // 2. دریافت URL از تنظیمات
        $settings = \App\Models\Settings::getSettings();
        $apiUrl = $settings->api_url ?? null;
        
        if (!$apiUrl) {
            throw new \Exception("API URL not set in settings. Please configure 'لینک API اقامت‌گران' in settings.");
        }
        
        $primaryApiUrl = $apiUrl;
        $fallbackApiUrl = 'http://127.0.0.1:8000/api/residents'; // fallback لوکال
        
        // \Log::info("Primary API URL: {$primaryApiUrl}");
        // \Log::info("Fallback API URL: {$fallbackApiUrl}");
        
        // 3. تلاش برای دریافت داده‌ها از API اصلی، سپس از fallback
        $apiUrl = $primaryApiUrl;
        $response = null;
        $httpCode = 0;
        
        // تلاش اول با API اصلی
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            // \Log::info("Attempt {$attempt}: Trying API URL: {$apiUrl}");
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // \Log::info("API Response - HTTP {$httpCode} from {$apiUrl}");
            
            if ($httpCode === 200) {
                // \Log::info("API request successful on attempt {$attempt}");
                break;
            } else {
                // \Log::warning("API request failed on attempt {$attempt}: HTTP {$httpCode}");
                
                // اگر تلاش اول ناموفق بود، از fallback استفاده کن
                if ($attempt === 1) {
                    $apiUrl = $fallbackApiUrl;
                    // \Log::info("Switching to fallback API: {$apiUrl}");
                } else {
                    // هر دو تلاش ناموفق بودند
                    throw new \Exception("Both APIs failed. Primary: HTTP {$httpCode} from {$primaryApiUrl}, Fallback: HTTP {$httpCode} from {$fallbackApiUrl}");
                }
            }
        }
        
        $residents = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decode error: " . json_last_error_msg());
        }
        
        if (empty($residents) || !is_array($residents)) {
            throw new \Exception("No data received from API");
        }
        
        $residents = array_values($residents);
        // \Log::info("API returned " . count($residents) . " residents");
        
        // 4. درج داده‌های جدید
        $createdCount = 0;
        foreach ($residents as $item) {
            if (!isset($item['resident_id'])) {
                continue;
            }
            
            \App\Models\Resident::create([
                'resident_id' => $item['resident_id'],
                'contract_id' => $item['contract_id'] ?? null,
                'unit_id' => $item['unit_id'] ?? null,
                'unit_name' => $item['unit_name'] ?? null,
                'unit_code' => $item['unit_code'] ?? null,
                'unit_desc' => $item['unit_desc'] ?? null,
                'unit_created_at' => $item['unit_created_at'] ?? null,
                'unit_updated_at' => $item['unit_updated_at'] ?? null,
                'room_id' => $item['room_id'] ?? null,
                'room_name' => $item['room_name'] ?? null,
                'room_code' => $item['room_code'] ?? null,
                'room_unit_id' => $item['room_unit_id'] ?? null,
                'room_bed_count' => $item['room_bed_count'] ?? null,
                'room_desc' => $item['room_desc'] ?? null,
                'room_type' => $item['room_type'] ?? null,
                'room_created_at' => $item['room_created_at'] ?? null,
                'room_updated_at' => $item['room_updated_at'] ?? null,
                'bed_id' => $item['bed_id'] ?? null,
                'bed_name' => $item['bed_name'] ?? null,
                'bed_code' => $item['bed_code'] ?? null,
                'bed_room_id' => $item['bed_room_id'] ?? null,
                'bed_state_ratio_resident' => $item['bed_state_ratio_resident'] ?? null,
                'bed_state' => $item['bed_state'] ?? null,
                'bed_desc' => $item['bed_desc'] ?? null,
                'bed_created_at' => $item['bed_created_at'] ?? null,
                'bed_updated_at' => $item['bed_updated_at'] ?? null,
                'contract_resident_id' => $item['contract_resident_id'] ?? null,
                'contract_payment_date' => $item['contract_payment_date'] ?? null,
                'contract_payment_date_jalali' => $item['contract_payment_date_jalali'] ?? null,
                'contract_bed_id' => $item['contract_bed_id'] ?? null,
                'contract_state' => $item['contract_state'] ?? null,
                'contract_start_date' => $item['contract_start_date'] ?? null,
                'contract_start_date_jalali' => $item['contract_start_date_jalali'] ?? null,
                'contract_end_date' => $item['contract_end_date'] ?? null,
                'contract_end_date_jalali' => $item['contract_end_date_jalali'] ?? null,
                'contract_created_at' => $item['contract_created_at'] ?? null,
                'contract_updated_at' => $item['contract_updated_at'] ?? null,
                'contract_deleted_at' => $item['contract_deleted_at'] ?? null,
                'resident_full_name' => $item['resident_full_name'] ?? null,
                'resident_phone' => $item['resident_phone'] ?? null,
                'resident_age' => $item['resident_age'] ?? null,
                'resident_birth_date' => $item['resident_birth_date'] ?? null,
                'resident_job' => $item['resident_job'] ?? null,
                'resident_referral_source' => $item['resident_referral_source'] ?? null,
                'resident_form' => $item['resident_form'] ?? false,
                'resident_document' => $item['resident_document'] ?? false,
                'resident_rent' => $item['resident_rent'] ?? false,
                'resident_trust' => $item['resident_trust'] ?? false,
                'resident_created_at' => $item['resident_created_at'] ?? null,
                'resident_updated_at' => $item['resident_updated_at'] ?? null,
                'resident_deleted_at' => $item['resident_deleted_at'] ?? null,
                'notes' => $item['notes'] ?? null,
                'delay' => $item['delay'] ?? 0,
                'last_synced_at' => now(),
            ]);
            
            $createdCount++;
        }
        
        // \Log::info("Created {$createdCount} new residents");
        
        // 5. ذخیره cache
        \Illuminate\Support\Facades\Cache::put('residents_last_sync', [
            'time' => now()->format('Y-m-d H:i:s'),
            'synced_count' => $createdCount,
            'created_count' => $createdCount,
            'updated_count' => 0,
            'deleted_count' => $deletedCount,
            'message' => "دیتابیس از {$apiUrl} جایگزین شد. حذف شده: {$deletedCount}, ایجاد شده: {$createdCount}",
        ], now()->addDays(7));
        
        $totalInDb = \App\Models\Resident::count();
        
        return response()->json([
            'success' => true,
            'message' => "همگام‌سازی موفق از {$apiUrl}: {$deletedCount} حذف، {$createdCount} ایجاد شد. مجموع: {$totalInDb} رکورد."
        ]);
        
    } catch (\Exception $e) {
        \Log::error('=== Sync failed ===', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'خطا: ' . $e->getMessage()
        ], 500);
    }
});

// Sync endpoint with live API
Route::post('/sync-data-live', function () {
    try {
        \Log::info('=== Starting sync with LIVE API ===');
        
        // 1. پاک کردن کل جدول
        $deletedCount = \App\Models\Resident::count();
        \App\Models\Resident::query()->delete();
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE residents AUTO_INCREMENT = 1');
        \Log::info("Deleted {$deletedCount} residents from database");
        
        // 2. دریافت داده‌ها از API هاست
        $apiUrl = 'http://atlasdorm.com/api/residents';
        \Log::info("Fetching data from LIVE API: {$apiUrl}");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception("LIVE API request failed: HTTP {$httpCode}");
        }
        
        $residents = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decode error: " . json_last_error_msg());
        }
        
        if (empty($residents) || !is_array($residents)) {
            throw new \Exception("No data received from LIVE API");
        }
        
        $residents = array_values($residents);
        \Log::info("LIVE API returned " . count($residents) . " residents");
        
        // 3. درج داده‌های جدید
        $createdCount = 0;
        foreach ($residents as $item) {
            if (!isset($item['resident_id'])) {
                continue;
            }
            
            \App\Models\Resident::create([
                'resident_id' => $item['resident_id'],
                'contract_id' => $item['contract_id'] ?? null,
                'unit_id' => $item['unit_id'] ?? null,
                'unit_name' => $item['unit_name'] ?? null,
                'unit_code' => $item['unit_code'] ?? null,
                'unit_desc' => $item['unit_desc'] ?? null,
                'unit_created_at' => $item['unit_created_at'] ?? null,
                'unit_updated_at' => $item['unit_updated_at'] ?? null,
                'room_id' => $item['room_id'] ?? null,
                'room_name' => $item['room_name'] ?? null,
                'room_code' => $item['room_code'] ?? null,
                'room_unit_id' => $item['room_unit_id'] ?? null,
                'room_bed_count' => $item['room_bed_count'] ?? null,
                'room_desc' => $item['room_desc'] ?? null,
                'room_type' => $item['room_type'] ?? null,
                'room_created_at' => $item['room_created_at'] ?? null,
                'room_updated_at' => $item['room_updated_at'] ?? null,
                'bed_id' => $item['bed_id'] ?? null,
                'bed_name' => $item['bed_name'] ?? null,
                'bed_code' => $item['bed_code'] ?? null,
                'bed_room_id' => $item['bed_room_id'] ?? null,
                'bed_state_ratio_resident' => $item['bed_state_ratio_resident'] ?? null,
                'bed_state' => $item['bed_state'] ?? null,
                'bed_desc' => $item['bed_desc'] ?? null,
                'bed_created_at' => $item['bed_created_at'] ?? null,
                'bed_updated_at' => $item['bed_updated_at'] ?? null,
                'contract_resident_id' => $item['contract_resident_id'] ?? null,
                'contract_payment_date' => $item['contract_payment_date'] ?? null,
                'contract_payment_date_jalali' => $item['contract_payment_date_jalali'] ?? null,
                'contract_bed_id' => $item['contract_bed_id'] ?? null,
                'contract_state' => $item['contract_state'] ?? null,
                'contract_start_date' => $item['contract_start_date'] ?? null,
                'contract_start_date_jalali' => $item['contract_start_date_jalali'] ?? null,
                'contract_end_date' => $item['contract_end_date'] ?? null,
                'contract_end_date_jalali' => $item['contract_end_date_jalali'] ?? null,
                'contract_created_at' => $item['contract_created_at'] ?? null,
                'contract_updated_at' => $item['contract_updated_at'] ?? null,
                'contract_deleted_at' => $item['contract_deleted_at'] ?? null,
                'resident_full_name' => $item['resident_full_name'] ?? null,
                'resident_phone' => $item['resident_phone'] ?? null,
                'resident_age' => $item['resident_age'] ?? null,
                'resident_birth_date' => $item['resident_birth_date'] ?? null,
                'resident_job' => $item['resident_job'] ?? null,
                'resident_referral_source' => $item['resident_referral_source'] ?? null,
                'resident_form' => $item['resident_form'] ?? false,
                'resident_document' => $item['resident_document'] ?? false,
                'resident_rent' => $item['resident_rent'] ?? false,
                'resident_trust' => $item['resident_trust'] ?? false,
                'resident_created_at' => $item['resident_created_at'] ?? null,
                'resident_updated_at' => $item['resident_updated_at'] ?? null,
                'resident_deleted_at' => $item['resident_deleted_at'] ?? null,
                'notes' => $item['notes'] ?? null,
                'delay' => $item['delay'] ?? 0,
                'last_synced_at' => now(),
            ]);
            
            $createdCount++;
        }
        
        \Log::info("Created {$createdCount} new residents from LIVE API");
        
        // 4. ذخیره cache
        \Illuminate\Support\Facades\Cache::put('residents_last_sync', [
            'time' => now()->format('Y-m-d H:i:s'),
            'synced_count' => $createdCount,
            'created_count' => $createdCount,
            'updated_count' => 0,
            'deleted_count' => $deletedCount,
            'message' => "دیتابیس از API هاست جایگزین شد. حذف شده: {$deletedCount}, ایجاد شده: {$createdCount}",
        ], now()->addDays(7));
        
        $totalInDb = \App\Models\Resident::count();
        
        return response()->json([
            'success' => true,
            'message' => "همگام‌سازی موفق از API هاست: {$deletedCount} حذف، {$createdCount} ایجاد شد. مجموع: {$totalInDb} رکورد."
        ]);
        
    } catch (\Exception $e) {
        \Log::error('=== LIVE Sync failed ===', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'خطا در همگام‌سازی از API هاست: ' . $e->getMessage()
        ], 500);
    }
});

// API endpoint for syncing residents
Route::post('/api/residents/sync', function () {
    try {
        // اجرای Job همگام‌سازی
        $job = new \App\Jobs\SyncResidentsFromApi();
        $job->handle();
        
        // دریافت آمار همگام‌سازی
        $lastSync = \Illuminate\Support\Facades\Cache::get('residents_last_sync');
        
        // بررسی تعداد واقعی در دیتابیس
        $totalInDb = \App\Models\Resident::count();
        $lastSyncedResident = \App\Models\Resident::orderBy('last_synced_at', 'desc')->first();
        $lastSyncTime = $lastSyncedResident && $lastSyncedResident->last_synced_at 
            ? $lastSyncedResident->last_synced_at->format('Y-m-d H:i:s') 
            : 'نامشخص';
        
        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => [
                'synced_count' => $lastSync['synced_count'] ?? 0,
                'created_count' => $lastSync['created_count'] ?? 0,
                'updated_count' => $lastSync['updated_count'] ?? 0,
                'total_in_db' => $totalInDb,
                'last_sync_time' => $lastSyncTime,
            ]
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error syncing residents from API route', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'خطا در همگام‌سازی داده‌ها: ' . $e->getMessage(),
        ], 500);
    }
})->middleware('web');

// API endpoint for sync status (برای بررسی اینکه آیا sync انجام شده یا نه)
Route::get('/api/residents/sync-status', function () {
    $lastSyncTime = \Illuminate\Support\Facades\Cache::get('residents_last_sync_time');
    
    if ($lastSyncTime) {
        $lastSync = \Illuminate\Support\Facades\Cache::get('residents_last_sync');
        return response()->json([
            'synced' => true,
            'last_sync_time' => $lastSyncTime->format('Y-m-d H:i:s'),
            'synced_count' => $lastSync['synced_count'] ?? 0,
            'created_count' => $lastSync['created_count'] ?? 0,
            'updated_count' => $lastSync['updated_count'] ?? 0,
        ]);
    }
    
    return response()->json([
        'synced' => false,
        'last_sync_time' => null,
    ]);
});

// API endpoint for last sync status
Route::get('/api/residents/last-sync', function () {
    // ابتدا از cache بخوان
    $lastSync = \Illuminate\Support\Facades\Cache::get('residents_last_sync');
    
    if ($lastSync && isset($lastSync['time']) && $lastSync['time'] !== null) {
        return response()->json([
            'time' => $lastSync['time'],
            'synced_count' => $lastSync['synced_count'] ?? 0,
            'created_count' => $lastSync['created_count'] ?? 0,
            'updated_count' => $lastSync['updated_count'] ?? 0,
            'message' => $lastSync['message'] ?? 'همگام‌سازی انجام شده است',
        ]);
    }
    
    // اگر cache وجود نداشت یا خالی بود، از آخرین sync در دیتابیس استفاده کن
    try {
        $lastSyncedResident = \App\Models\Resident::orderBy('last_synced_at', 'desc')->first();
        $totalCount = \App\Models\Resident::count();
        
        if ($lastSyncedResident && $lastSyncedResident->last_synced_at) {
            $time = $lastSyncedResident->last_synced_at instanceof \Carbon\Carbon 
                ? $lastSyncedResident->last_synced_at->format('Y-m-d H:i:s')
                : $lastSyncedResident->last_synced_at;
                
            return response()->json([
                'time' => $time,
                'synced_count' => $totalCount,
                'created_count' => $totalCount,
                'updated_count' => 0,
                'message' => 'همگام‌سازی انجام شده است (از دیتابیس)',
            ]);
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error getting last sync from database', [
            'error' => $e->getMessage()
        ]);
    }
    
    // اگر هیچ داده‌ای پیدا نشد
    return response()->json([
        'time' => null,
        'synced_count' => 0,
        'created_count' => 0,
        'updated_count' => 0,
        'message' => 'هنوز همگام‌سازی انجام نشده است',
    ]);
});


// Route های API برای حذف
Route::post('/api/reports/bulk-delete', function () {
    // این Route برای حذف گروهی استفاده می‌شود
    return response()->json(['success' => true]);
})->name('reports.bulk-delete');

Route::post('/api/categories/bulk-delete', function () {
    // این Route برای حذف گروهی استفاده می‌شود
    return response()->json(['success' => true]);
})->name('categories.bulk-delete');

// API endpoint برای دریافت داده‌های کاربر
Route::get('/api/resident/{residentId}', function ($residentId) {
    try {
        $resident = \App\Models\Resident::where('resident_id', $residentId)->first();
        
        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر یافت نشد'
            ], 404);
        }

        // دریافت گزارش‌های این کاربر
        $residentReports = \App\Models\ResidentReport::where('resident_id', $residentId)
            ->with('report', 'report.category')
            ->get()
            ->groupBy(function ($rr) {
                return $rr->report->api_endpoint_name ?? 'other';
            })
            ->map(function ($reports, $key) {
                return $reports->map(function ($rr) {
                    return [
                        'id' => $rr->id,
                        'report_id' => $rr->report_id,
                        'report_title' => $rr->report->title ?? null,
                        'report_category' => $rr->report->category->name ?? null,
                        'report_negative_score' => $rr->report->negative_score ?? 0,
                        'description' => $rr->description,
                        'notes' => $rr->notes,
                        'has_been_sent' => $rr->has_been_sent,
                        'is_checked' => $rr->is_checked,
                        'created_at' => $rr->created_at ? $rr->created_at->format('Y-m-d H:i:s') : null,
                    ];
                });
            });

        return response()->json([
            'success' => true,
            'data' => [
                'resident_id' => $resident->resident_id,
                'contract_id' => $resident->contract_id,
                'full_name' => $resident->resident_full_name,
                'phone' => $resident->resident_phone,
                'reports' => $residentReports,
            ]
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error fetching resident data', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت اطلاعات: ' . $e->getMessage()
        ], 500);
    }
})->name('api.resident.data');

// API endpoint برای دریافت همه کاربران با تخلف‌ها
Route::get('/api/residents/all', function () {
    try {
        $residents = \App\Models\Resident::all();
        
        $residentsData = $residents->map(function ($resident) {
            // دریافت گزارش‌های این کاربر
            $residentReports = \App\Models\ResidentReport::where('resident_id', $resident->resident_id)
                ->with('report', 'report.category')
                ->get()
                ->groupBy(function ($rr) {
                    return $rr->report->api_endpoint_name ?? 'other';
                })
                ->map(function ($reports, $key) {
                    return $reports->map(function ($rr) {
                        return [
                            'id' => $rr->id,
                            'report_id' => $rr->report_id,
                            'report_title' => $rr->report->title ?? null,
                            'report_category' => $rr->report->category->name ?? null,
                            'report_negative_score' => $rr->report->negative_score ?? 0,
                            'description' => $rr->description,
                            'notes' => $rr->notes,
                            'has_been_sent' => $rr->has_been_sent,
                            'is_checked' => $rr->is_checked,
                            'created_at' => $rr->created_at ? $rr->created_at->format('Y-m-d H:i:s') : null,
                        ];
                    });
                });

            return [
                'resident_id' => $resident->resident_id,
                'contract_id' => $resident->contract_id,
                'full_name' => $resident->resident_full_name,
                'phone' => $resident->resident_phone,
                'reports' => $residentReports,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $residentsData->count(),
            'data' => $residentsData,
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error fetching all residents data', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت اطلاعات: ' . $e->getMessage()
        ], 500);
    }
})->name('api.residents.all');

// API endpoint برای دریافت لیست همه گزارش‌ها با endpoint names
Route::get('/api/reports/endpoints', function () {
    try {
        $reports = \App\Models\Report::with('category')
            ->orderBy('title')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'title' => $report->title,
                    'category' => $report->category->name ?? null,
                    'api_endpoint_name' => $report->api_endpoint_name,
                    'negative_score' => $report->negative_score,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'خطا در دریافت اطلاعات: ' . $e->getMessage()
        ], 500);
    }
})->name('api.reports.endpoints');
