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
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/reports', ReportsIndex::class)->name('reports.index');
Route::get('/reports/create', ReportsCreate::class)->name('reports.create');
Route::get('/reports/edit/{id}', ReportsEdit::class)->name('reports.edit');
Route::get('/categories', CategoriesIndex::class)->name('categories.index');
Route::get('/categories/create', CategoriesCreate::class)->name('categories.create');
Route::get('/categories/edit/{id}', CategoriesEdit::class)->name('categories.edit');

Route::get('/residents', Units::class)->name('residents.units');
Route::get('/resident-reports', ResidentReports::class)->name('residents.reports');
Route::get('/resident-reports/notifications', \App\Livewire\Residents\NotificationReports::class)->name('residents.notification-reports');
Route::get('/residents/expired-today', ExpiredToday::class)->name('residents.expired-today');
Route::get('/residents/group-sms', \App\Livewire\Residents\GroupSms::class)->name('residents.group-sms');
// پیام‌های ساده
Route::get('/sms', \App\Livewire\Sms\Index::class)->name('sms.index');
Route::get('/sms/manual', \App\Livewire\Sms\Manual::class)->name('sms.manual');
Route::get('/sms/group', \App\Livewire\Sms\Group::class)->name('sms.group');
Route::get('/sms/sent', \App\Livewire\Sms\SentMessages::class)->name('sms.sent');

// پیام‌های الگویی
Route::get('/sms/pattern-manual', \App\Livewire\Sms\PatternManual::class)->name('sms.pattern-manual');
Route::get('/sms/pattern-group', \App\Livewire\Sms\PatternGroup::class)->name('sms.pattern-group');
Route::get('/sms/pattern-test', \App\Livewire\Sms\PatternTest::class)->name('sms.pattern-test');
Route::get('/sms/auto', \App\Livewire\Sms\Auto::class)->name('sms.auto');
Route::get('/sms/violation-sms', \App\Livewire\Sms\ViolationSms::class)->name('sms.violation-sms');
Route::get('/reports/violations', \App\Livewire\Reports\Violations::class)->name('reports.violations');
Route::get('/blacklists', \App\Livewire\Blacklists\Index::class)->name('blacklists.index');
Route::get('/patterns', \App\Livewire\Patterns\Index::class)->name('patterns.index');
Route::get('/patterns/create', \App\Livewire\Patterns\Index::class)->name('patterns.create');
Route::get('/variables', \App\Livewire\Variables\Index::class)->name('variables.index');
Route::get('/variables/create', \App\Livewire\Variables\Index::class)->name('variables.create');
Route::get('/sender-numbers', \App\Livewire\Admin\SenderNumbers::class)->name('sender-numbers.index');
Route::get('/api-keys', \App\Livewire\Admin\ApiKeyManager::class)->name('api-keys.index');
Route::get('/constants', \App\Livewire\Constants\Index::class)->name('constants.index');
Route::get('/table-names', \App\Livewire\TableNames\Index::class)->name('table-names.index');
Route::get('/settings', \App\Livewire\Settings\Index::class)->name('settings.index');

// پیام‌های خوش‌آمدگویی
Route::get('/welcome-messages', \App\Livewire\WelcomeMessages\Index::class)->name('welcome-messages.index');
Route::get('/welcome-messages/logs', [\App\Http\Controllers\WelcomeMessageController::class, 'logs'])->name('welcome-messages.logs');
Route::post('/welcome-messages/process', [\App\Http\Controllers\WelcomeMessageController::class, 'process'])->name('welcome-messages.process');

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
        \Log::info('=== Starting sync using settings API ===');
        
        // اجرای Job همگام‌سازی اصلاح شده
        $job = new \App\Jobs\SyncResidentsFromApi();
        $job->handle();
        
        // دریافت آمار همگام‌سازی
        $totalInDb = \App\Models\Resident::count();
        $lastSyncedResident = \App\Models\Resident::orderBy('last_synced_at', 'desc')->first();
        $lastSyncTime = $lastSyncedResident && $lastSyncedResident->last_synced_at 
            ? $lastSyncedResident->last_synced_at->format('Y-m-d H:i:s') 
            : 'نامشخص';
        
        \Log::info("Sync completed successfully", [
            'total_in_db' => $totalInDb,
            'last_sync_time' => $lastSyncTime
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'همگام‌سازی با موفقیت انجام شد',
            'data' => [
                'total_in_db' => $totalInDb,
                'last_sync_time' => $lastSyncTime,
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('=== Sync failed ===', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'خطا در همگام‌سازی: ' . $e->getMessage()
        ], 500);
    }
});

// Sync endpoint with live API
        
        $residents = array_values($residents);
        \Log::info("API returned " . count($residents) . " residents");
        
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
                'room_id' => $item['room_id'] ?? null,
                'room_name' => $item['room_name'] ?? null,
                'room_code' => $item['room_code'] ?? null,
                'bed_id' => $item['bed_id'] ?? null,
                'bed_name' => $item['bed_name'] ?? null,
                'bed_code' => $item['bed_code'] ?? null,
                'contract_payment_date' => $item['contract_payment_date'] ?? null,
                'contract_payment_date_jalali' => $item['contract_payment_date_jalali'] ?? null,
                'contract_state' => $item['contract_state'] ?? null,
                'contract_start_date' => $item['contract_start_date'] ?? null,
                'contract_start_date_jalali' => $item['contract_start_date_jalali'] ?? null,
                'resident_full_name' => $item['resident_full_name'] ?? null,
                'resident_phone' => $item['resident_phone'] ?? null,
                'resident_age' => $item['resident_age'] ?? null,
                'resident_job' => $item['resident_job'] ?? null,
                'resident_referral_source' => $item['resident_referral_source'] ?? null,
                'resident_form' => $item['resident_form'] ?? false,
                'resident_document' => $item['resident_document'] ?? false,
                'resident_rent' => $item['resident_rent'] ?? false,
                'resident_trust' => $item['resident_trust'] ?? false,
                'delay' => $item['delay'] ?? 0,
            ]);
            
            $createdCount++;
        }
        
        \Log::info("Created {$createdCount} new residents");
        
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
                'room_id' => $item['room_id'] ?? null,
                'room_name' => $item['room_name'] ?? null,
                'room_code' => $item['room_code'] ?? null,
                'bed_id' => $item['bed_id'] ?? null,
                'bed_name' => $item['bed_name'] ?? null,
                'bed_code' => $item['bed_code'] ?? null,
                'contract_payment_date' => $item['contract_payment_date'] ?? null,
                'contract_payment_date_jalali' => $item['contract_payment_date_jalali'] ?? null,
                'contract_state' => $item['contract_state'] ?? null,
                'contract_start_date' => $item['contract_start_date'] ?? null,
                'contract_start_date_jalali' => $item['contract_start_date_jalali'] ?? null,
                'resident_full_name' => $item['resident_full_name'] ?? null,
                'resident_phone' => $item['resident_phone'] ?? null,
                'resident_age' => $item['resident_age'] ?? null,
                'resident_job' => $item['resident_job'] ?? null,
                'resident_referral_source' => $item['resident_referral_source'] ?? null,
                'resident_form' => $item['resident_form'] ?? false,
                'resident_document' => $item['resident_document'] ?? false,
                'resident_rent' => $item['resident_rent'] ?? false,
                'resident_trust' => $item['resident_trust'] ?? false,
                'delay' => $item['delay'] ?? 0,
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
    $settings = \App\Models\Settings::getSettings();
    $refreshInterval = $settings->refresh_interval ?? 5;
    
    if ($lastSyncTime) {
        $lastSync = \Illuminate\Support\Facades\Cache::get('residents_last_sync');
        return response()->json([
            'synced' => true,
            'last_sync_time' => $lastSyncTime->format('Y-m-d H:i:s'),
            'refresh_interval' => $refreshInterval,
            'synced_count' => $lastSync['synced_count'] ?? 0,
            'created_count' => $lastSync['created_count'] ?? 0,
            'updated_count' => $lastSync['updated_count'] ?? 0,
        ]);
    }
    
    return response()->json([
        'synced' => false,
        'last_sync_time' => null,
        'refresh_interval' => $refreshInterval,
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
