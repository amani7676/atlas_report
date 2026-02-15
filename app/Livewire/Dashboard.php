<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Report;
use App\Models\Category;
use App\Models\SmsMessageResident;
use App\Models\ResidentReport;
use App\Services\SmsDeliveryService;

class Dashboard extends Component
{
    public $totalReports;
    public $totalCategories;
    public $recentReports;
    public $recentSentMessages;
    public $totalSentMessages;
    public $failedMessages;
    public $orphanedRecordsCount = 0;
    public $deliveryStats = [];
    public $lastDeliveryCheck = null;

    public function mount()
    {
        $this->loadData();
    }
    
    public function loadData()
    {
        $this->totalReports = Report::count();
        $this->totalCategories = Category::count();
        $this->recentReports = Report::with('category')
            ->latest()
            ->take(5)
            ->get();
        
        // دریافت پیام‌های ارسال شده اخیر
        $this->recentSentMessages = SmsMessageResident::with(['smsMessage'])
            ->latest()
            ->take(5)
            ->get();
        
        // آمار پیام‌ها
        $this->totalSentMessages = SmsMessageResident::count();
        $this->failedMessages = SmsMessageResident::where('status', 'failed')->count();
        
        // دریافت آمار وضعیت دلیوری
        $deliveryService = new SmsDeliveryService();
        $this->deliveryStats = $deliveryService->getDeliveryStats();
        
        // دریافت آخرین زمان بررسی دلیوری
        $lastCheck = \App\Models\Constant::where('key', 'last_delivery_check')->first();
        $this->lastDeliveryCheck = $lastCheck ? $lastCheck->value : null;
        
        // شمارش رکوردهای یتیم
        $this->countOrphanedRecords();
    }
    
    public function countOrphanedRecords()
    {
        // دریافت تمام resident_id های موجود در جدول residents
        $existingResidentIds = \App\Models\Resident::pluck('resident_id')->toArray();
        
        $orphanedCount = 0;
        
        // شمارش گزارش‌هایی که resident_id ندارند (null)
        $orphanedCount += ResidentReport::whereNull('resident_id')->count();
        
        // شمارش پیام‌هایی که resident_id ندارند (null)
        $orphanedCount += SmsMessageResident::whereNull('resident_id')->count();
        
        // اگر اقامتگری وجود دارد، رکوردهای با resident_id نامعتبر را هم بشمار
        if (!empty($existingResidentIds)) {
            // شمارش گزارش‌های اقامتگرانی که دیگر وجود ندارند
            $orphanedCount += ResidentReport::whereNotNull('resident_id')
                ->whereNotIn('resident_id', $existingResidentIds)
                ->count();
            
            // شمارش پیام‌های اقامتگرانی که دیگر وجود ندارند
            $orphanedCount += SmsMessageResident::whereNotNull('resident_id')
                ->whereNotIn('resident_id', $existingResidentIds)
                ->count();
        }
        
        $this->orphanedRecordsCount = $orphanedCount;
    }
    
    public function cleanupOrphanedRecords()
    {
        try {
            // دریافت تمام resident_id های موجود در جدول residents
            $existingResidentIds = \App\Models\Resident::pluck('resident_id')->toArray();
            
            $deletedReports = 0;
            $deletedMessages = 0;
            $deletedJobs = 0;
            $cleanedLogs = 0;
            $cleanedFiles = 0;
            
            // 1. حذف گزارش‌هایی که resident_id ندارند (null)
            $deletedNullReports = ResidentReport::whereNull('resident_id')->delete();
            $deletedReports += $deletedNullReports;
            
            // 2. حذف پیام‌هایی که resident_id ندارند (null)
            $deletedNullMessages = SmsMessageResident::whereNull('resident_id')->delete();
            $deletedMessages += $deletedNullMessages;
            
            // 3. اگر هیچ اقامتگری وجود ندارد، بقیه رکوردها را هم حذف کن
            if (empty($existingResidentIds)) {
                $deletedAllReports = ResidentReport::whereNotNull('resident_id')->delete();
                $deletedAllMessages = SmsMessageResident::whereNotNull('resident_id')->delete();
                $deletedReports += $deletedAllReports;
                $deletedMessages += $deletedAllMessages;
            } else {
                // 4. حذف گزارش‌های اقامتگرانی که دیگر وجود ندارند
                $deletedOrphanReports = ResidentReport::whereNotNull('resident_id')
                    ->whereNotIn('resident_id', $existingResidentIds)
                    ->delete();
                $deletedReports += $deletedOrphanReports;
                
                // 5. حذف پیام‌های اقامتگرانی که دیگر وجود ندارند
                $deletedOrphanMessages = SmsMessageResident::whereNotNull('resident_id')
                    ->whereNotIn('resident_id', $existingResidentIds)
                    ->delete();
                $deletedMessages += $deletedOrphanMessages;
            }
            
            // 6. خالی کردن جدول jobs
            try {
                $deletedJobs = \DB::table('jobs')->count();
                \DB::table('jobs')->truncate();
            } catch (\Exception $e) {
                \Log::warning('Could not truncate jobs table: ' . $e->getMessage());
                $deletedJobs = 0;
            }
            
            // 7. پاک‌سازی فایل‌های لاگ
            $cleanedLogs = $this->cleanupLogFiles();
            
            // 8. پاک‌سازی فایل‌های قدیمی
            $cleanedFiles = $this->cleanupOldFiles();
            
            // بارگذاری مجدد داده‌ها
            $this->loadData();
            
            // نمایش پیام موفقیت
            $message = "پاک‌سازی با موفقیت انجام شد.";
            if ($deletedReports > 0 || $deletedMessages > 0 || $deletedJobs > 0 || $cleanedLogs > 0 || $cleanedFiles > 0) {
                $message .= " {$deletedReports} گزارش، {$deletedMessages} پیام، {$deletedJobs} job، {$cleanedLogs} لاگ و {$cleanedFiles} فایل قدیمی حذف شدند.";
                if ($deletedNullReports > 0) {
                    $message .= " ({$deletedNullReports} گزارش بدون resident_id)";
                }
                if ($deletedNullMessages > 0) {
                    $message .= " ({$deletedNullMessages} پیام بدون resident_id)";
                }
            } else {
                $message .= " هیچ رکورد یتیمی یا فایل قدیمی برای حذف وجود نداشت.";
            }
            
            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => $message
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error cleaning up orphaned records', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در پاک‌سازی: ' . $e->getMessage()
            ]);
        }
    }
    
    public function updateDeliveryStatus()
    {
        try {
            $deliveryService = new SmsDeliveryService();
            $result = $deliveryService->updateDeliveryStatus();
            
            // ذخیره آخرین زمان بررسی
            \App\Models\Constant::updateOrCreate(
                ['key' => 'last_delivery_check'],
                ['value' => now()->format('Y-m-d H:i:s')]
            );
            
            // بارگذاری مجدد داده‌ها
            $this->loadData();
            
            $this->dispatch('showAlert', [
                'type' => $result['success'] ? 'success' : 'error',
                'title' => $result['success'] ? 'موفقیت!' : 'خطا!',
                'text' => $result['message']
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in updateDeliveryStatus', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در به‌روزرسانی وضعیت دلیوری: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * پاک‌سازی فایل‌های لاگ
     */
    private function cleanupLogFiles()
    {
        $cleanedCount = 0;
        
        try {
            // مسیرهای لاگ لاراول
            $logPaths = [
                storage_path('logs/laravel.log'),
                storage_path('logs/laravel-' . date('Y-m-d') . '.log'),
                storage_path('logs/app.log'),
                storage_path('logs/error.log'),
                storage_path('logs/custom.log'),
            ];
            
            foreach ($logPaths as $logPath) {
                if (file_exists($logPath)) {
                    // خالی کردن فایل لاگ به جای حذف کامل
                    if (file_put_contents($logPath, '') !== false) {
                        $cleanedCount++;
                        \Log::info("Log file cleaned: {$logPath}");
                    }
                }
            }
            
            // پاک‌سازی لاگ‌های قدیمی (بیشتر از ۷ روز)
            $logDir = storage_path('logs');
            if (is_dir($logDir)) {
                $files = glob($logDir . '/laravel-*.log');
                foreach ($files as $file) {
                    if (filemtime($file) < strtotime('-7 days')) {
                        if (unlink($file)) {
                            $cleanedCount++;
                            \Log::info("Old log file deleted: {$file}");
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Error cleaning log files', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $cleanedCount;
    }
    
    /**
     * پاک‌سازی فایل‌های قدیمی و کش
     */
    private function cleanupOldFiles()
    {
        $cleanedCount = 0;
        
        try {
            // مسیرهای پاک‌سازی
            $cleanupPaths = [
                storage_path('framework/cache/'),
                storage_path('framework/sessions/'),
                storage_path('framework/views/'),
                storage_path('framework/testing/'),
                storage_path('app/public/'),
                public_path('storage/temp/'),
            ];
            
            foreach ($cleanupPaths as $path) {
                if (is_dir($path)) {
                    $this->deleteDirectoryContents($path);
                    $cleanedCount++;
                    \Log::info("Directory cleaned: {$path}");
                }
            }
            
            // پاک‌سازی فایل‌های موقت
            $tempPaths = [
                sys_get_temp_dir() . '/atlas_report_*',
                storage_path('temp/'),
                storage_path('tmp/'),
            ];
            
            foreach ($tempPaths as $pattern) {
                $files = glob($pattern);
                foreach ($files as $file) {
                    if (is_file($file)) {
                        if (unlink($file)) {
                            $cleanedCount++;
                            \Log::info("Temp file deleted: {$file}");
                        }
                    } elseif (is_dir($file)) {
                        $this->deleteDirectoryContents($file);
                        if (rmdir($file)) {
                            $cleanedCount++;
                            \Log::info("Temp directory deleted: {$file}");
                        }
                    }
                }
            }
            
            // پاک‌سازی کش مرورگر
            $cachePaths = [
                public_path('storage/cache/'),
                public_path('js/cache/'),
                public_path('css/cache/'),
            ];
            
            foreach ($cachePaths as $path) {
                if (is_dir($path)) {
                    $this->deleteDirectoryContents($path);
                    $cleanedCount++;
                    \Log::info("Cache directory cleaned: {$path}");
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Error cleaning old files', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $cleanedCount;
    }
    
    /**
     * حذف محتویات یک دایرکتوری
     */
    private function deleteDirectoryContents($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->deleteDirectoryContents($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
