<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class TestAutoDelete extends AutoDeleteJob
{
    /**
     * Execute the main job logic.
     */
    protected function execute()
    {
        Log::info('TestAutoDelete job is executing...');
        
        // شبیه‌سازی کاری که انجام می‌شود
        sleep(1);
        
        Log::info('TestAutoDelete job completed!');
    }
}
