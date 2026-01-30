<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoPruneJobs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // با هر درخواست، وضعیت jobs را بررسی کن
        if ($this->shouldPruneJobs()) {
            $this->pruneJobs();
        }

        return $next($request);
    }

    /**
     * Check if jobs should be pruned
     */
    private function shouldPruneJobs()
    {
        // فقط هر 10 دقیقه یک بار اجرا شو
        $lastPrune = cache()->get('last_jobs_prune', 0);
        $now = now()->timestamp;
        
        if ($now - $lastPrune < 600) { // 10 دقیقه
            return false;
        }

        // تعداد jobs را بررسی کن
        $jobCount = DB::table('jobs')->count();
        
        // فقط اگر بیشتر از 100 تا بود، پاکسازی کن
        return $jobCount > 100;
    }

    /**
     * Prune old jobs
     */
    private function pruneJobs()
    {
        try {
            // پاکسازی jobs قدیمی‌تر از 5 دقیقه
            $deletedCount = DB::table('jobs')
                ->where('created_at', '<', now()->subMinutes(5))
                ->delete();

            // پاکسازی job batches تمام شده
            DB::table('job_batches')
                ->where('pending_jobs', 0)
                ->where('finished_at', '<', now()->subMinutes(30))
                ->delete();

            // پاکسازی failed jobs قدیمی
            DB::table('failed_jobs')
                ->where('failed_at', '<', now()->subHours(2))
                ->delete();

            // ذخیره زمان آخرین پاکسازی
            cache()->put('last_jobs_prune', now()->timestamp, 3600);

            if ($deletedCount > 0) {
                Log::info("Auto-pruned {$deletedCount} old jobs");
            }

        } catch (\Exception $e) {
            Log::error('Auto-prune jobs error: ' . $e->getMessage());
        }
    }
}
