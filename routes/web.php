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
Route::get('/blacklists', \App\Livewire\Blacklists\Index::class)->name('blacklists.index');
Route::get('/patterns', \App\Livewire\Patterns\Index::class)->name('patterns.index');
Route::get('/patterns/create', \App\Livewire\Patterns\Index::class)->name('patterns.create');
Route::get('/variables', \App\Livewire\Variables\Index::class)->name('variables.index');
Route::get('/variables/create', \App\Livewire\Variables\Index::class)->name('variables.create');
Route::get('/sender-numbers', \App\Livewire\Admin\SenderNumbers::class)->name('sender-numbers.index');
Route::get('/api-keys', \App\Livewire\Admin\ApiKeyManager::class)->name('api-keys.index');
Route::get('/constants', \App\Livewire\Constants\Index::class)->name('constants.index');
Route::get('/table-names', \App\Livewire\TableNames\Index::class)->name('table-names.index');

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
