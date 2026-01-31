<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class StatsBar extends Component
{
    public $orphanedRecordsCount = 0;

    public function mount()
    {
        $this->updateOrphanedRecordsCount();
    }

    public function updateOrphanedRecordsCount()
    {
        try {
            // دریافت تمام resident_id های موجود در جدول residents
            $existingResidentIds = \App\Models\Resident::pluck('resident_id')->toArray();
            
            $orphanedCount = 0;
            
            // شمارش گزارش‌هایی که resident_id ندارند (null)
            $orphanedCount += \App\Models\ResidentReport::whereNull('resident_id')->count();
            
            // شمارش پیام‌هایی که resident_id ندارند (null)
            $orphanedCount += \App\Models\SmsMessageResident::whereNull('resident_id')->count();
            
            // اگر اقامتگری وجود دارد، رکوردهای با resident_id نامعتبر را هم بشمار
            if (!empty($existingResidentIds)) {
                // شمارش گزارش‌های اقامتگرانی که دیگر وجود ندارند
                $orphanedCount += \App\Models\ResidentReport::whereNotNull('resident_id')
                    ->whereNotIn('resident_id', $existingResidentIds)
                    ->count();
                
                // شمارش پیام‌های اقامتگرانی که دیگر وجود ندارند
                $orphanedCount += \App\Models\SmsMessageResident::whereNotNull('resident_id')
                    ->whereNotIn('resident_id', $existingResidentIds)
                    ->count();
            }
            
            $this->orphanedRecordsCount = $orphanedCount;
        } catch (\Exception $e) {
            $this->orphanedRecordsCount = 0;
        }
    }

    public function cleanupOrphanedRecords()
    {
        try {
            // پاک کردن کامل جدول jobs قبل از پاکسازی داده‌ها
            $this->clearJobsTable();
            
            // دریافت تمام resident_id های موجود در جدول residents
            $existingResidentIds = \App\Models\Resident::pluck('resident_id')->toArray();
            
            $deletedReports = 0;
            $deletedMessages = 0;
            $deletedJobs = 0;
            
            // 1. حذف گزارش‌هایی که resident_id ندارند (null)
            $deletedNullReports = \App\Models\ResidentReport::whereNull('resident_id')->delete();
            $deletedReports += $deletedNullReports;
            
            // 2. حذف پیام‌هایی که resident_id ندارند (null)
            $deletedNullMessages = \App\Models\SmsMessageResident::whereNull('resident_id')->delete();
            $deletedMessages += $deletedNullMessages;
            
            // 3. اگر هیچ اقامتگری وجود ندارد، بقیه رکوردها را هم حذف کن
            if (empty($existingResidentIds)) {
                $deletedAllReports = \App\Models\ResidentReport::whereNotNull('resident_id')->delete();
                $deletedAllMessages = \App\Models\SmsMessageResident::whereNotNull('resident_id')->delete();
                $deletedReports += $deletedAllReports;
                $deletedMessages += $deletedAllMessages;
            } else {
                // 4. حذف گزارش‌های اقامتگرانی که دیگر وجود ندارند
                $deletedOrphanReports = \App\Models\ResidentReport::whereNotNull('resident_id')
                    ->whereNotIn('resident_id', $existingResidentIds)
                    ->delete();
                $deletedReports += $deletedOrphanReports;
                
                // 5. حذف پیام‌های اقامتگرانی که دیگر وجود ندارند
                $deletedOrphanMessages = \App\Models\SmsMessageResident::whereNotNull('resident_id')
                    ->whereNotIn('resident_id', $existingResidentIds)
                    ->delete();
                $deletedMessages += $deletedOrphanMessages;
            }
            
            // بارگذاری مجدد داده‌ها
            $this->updateOrphanedRecordsCount();
            
            // نمایش پیام موفقیت
            $message = "پاک‌سازی با موفقیت انجام شد.";
            if ($deletedReports > 0 || $deletedMessages > 0 || $deletedJobs > 0) {
                $message .= " {$deletedReports} گزارش، {$deletedMessages} پیام و {$deletedJobs} job حذف شدند.";
                if ($deletedNullReports > 0) {
                    $message .= " ({$deletedNullReports} گزارش بدون resident_id)";
                }
                if ($deletedNullMessages > 0) {
                    $message .= " ({$deletedNullMessages} پیام بدون resident_id)";
                }
            } else {
                $message .= " هیچ رکورد یتیمی برای حذف وجود نداشت.";
            }
            
            $this->dispatch('showToast', 
                title: 'موفقیت', 
                message: $message, 
                type: 'success'
            );
            
        } catch (\Exception $e) {
            \Log::error('Error cleaning up orphaned records', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->dispatch('showToast', 
                title: 'خطا', 
                message: 'خطا در پاک‌سازی: ' . $e->getMessage(), 
                type: 'error'
            );
        }
    }

    /**
     * پاک کردن کامل جدول jobs و ریست کردن شماره‌گذاری
     */
    private function clearJobsTable()
    {
        try {
            $deletedCount = \Illuminate\Support\Facades\DB::table('jobs')->delete();
            \Illuminate\Support\Facades\DB::table('job_batches')->delete();
            \Illuminate\Support\Facades\DB::table('failed_jobs')->delete();
            
            // ریست کردن auto-increment ID برای شروع از 1
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE jobs AUTO_INCREMENT = 1');
            
            if ($deletedCount > 0) {
                \Log::info("Jobs table cleared and ID reset during orphaned records cleanup", [
                    'deleted_jobs' => $deletedCount
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to clear jobs table: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.layout.stats-bar');
    }
}
