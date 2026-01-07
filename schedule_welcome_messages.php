<?php

/**
 * Scheduler for Welcome Messages
 * This script should be run every minute to process welcome messages
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

// Run the welcome messages processing command
try {
    Artisan::call('welcome-messages:process');
    echo "Welcome messages processed successfully at " . date('Y-m-d H:i:s') . "\n";
} catch (Exception $e) {
    echo "Error processing welcome messages: " . $e->getMessage() . "\n";
    exit(1);
}
