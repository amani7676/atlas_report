<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessWelcomeMessages;

class ProcessWelcomeMessagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'welcome-messages:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send welcome messages to eligible residents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting welcome messages processing...');
        
        try {
            $job = new ProcessWelcomeMessages();
            $job->handle();
            
            $this->info('Welcome messages processing completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error processing welcome messages: ' . $e->getMessage());
            
            return 1;
        }
        
        return 0;
    }
}
