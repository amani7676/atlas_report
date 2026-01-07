<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WelcomeMessageService;
use Illuminate\Support\Facades\Log;

class ProcessWelcomeMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'welcome:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send welcome messages to eligible residents';

    /**
     * Execute the console command.
     */
    public function handle(WelcomeMessageService $welcomeMessageService)
    {
        $this->info('Starting welcome message processing...');
        
        try {
            $welcomeMessageService->processWelcomeMessages();
            $this->info('Welcome message processing completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error processing welcome messages: ' . $e->getMessage());
            Log::error('Welcome message command failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
