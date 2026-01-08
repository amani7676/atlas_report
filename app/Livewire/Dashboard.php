<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Report;
use App\Models\Category;
use App\Models\SmsMessageResident;
use App\Models\ResidentReport;

class Dashboard extends Component
{
    public $totalReports;
    public $totalCategories;
    public $recentReports;
    public $recentSentMessages;
    public $totalSentMessages;
    public $failedMessages;
    public $orphanedRecordsCount = 0;

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
            
            // بارگذاری مجدد داده‌ها
            $this->loadData();
            
            // نمایش پیام موفقیت
            $message = "پاک‌سازی با موفقیت انجام شد.";
            if ($deletedReports > 0 || $deletedMessages > 0) {
                $message .= " {$deletedReports} گزارش و {$deletedMessages} پیام حذف شدند.";
                if ($deletedNullReports > 0) {
                    $message .= " ({$deletedNullReports} گزارش بدون resident_id)";
                }
                if ($deletedNullMessages > 0) {
                    $message .= " ({$deletedNullMessages} پیام بدون resident_id)";
                }
            } else {
                $message .= " هیچ رکورد یتیمی برای حذف وجود نداشت.";
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

    public function render()
    {
        return view('livewire.dashboard');
    }
}
