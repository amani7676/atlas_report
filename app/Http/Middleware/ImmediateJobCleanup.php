<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImmediateJobCleanup
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
        // با هر درخواست، jobs قدیمی را فوراً پاک کن
        $this->cleanupOldJobs();

        return $next($request);
    }

    /**
     * Immediately clean up old jobs
     */
    private function cleanupOldJobs()
    {
        try {
            // پاکسازی jobs قدیمی‌تر از 1 دقیقه
            $oneMinuteAgo = now()->subMinute()->timestamp;
            
            $deletedCount = DB::table('jobs')
                ->where('created_at', '<', $oneMinuteAgo)
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Immediately cleaned {$deletedCount} old jobs");
            }

        } catch (\Exception $e) {
            Log::error('Immediate job cleanup error: ' . $e->getMessage());
        }
    }
}
