<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Testing Livewire Components ===" . PHP_EOL;

try {
    // Test if Livewire is available
    if (class_exists('\Livewire\Livewire')) {
        echo "Livewire: OK" . PHP_EOL;
    } else {
        echo "Livewire: NOT FOUND" . PHP_EOL;
    }
    
    // Test specific Livewire components
    $components = [
        'App\\Livewire\\Dashboard',
        'App\\Livewire\\Residents\\ResidentReports',
        'App\\Livewire\\Residents\\NotificationReports',
        'App\\Livewire\\Residents\\GroupSms',
    ];
    
    foreach ($components as $component) {
        if (class_exists($component)) {
            echo "$component: EXISTS" . PHP_EOL;
        } else {
            echo "$component: NOT FOUND" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Testing View Rendering ===" . PHP_EOL;
    
    // Test rendering the dashboard view
    try {
        $view = view('dashboard');
        echo "Dashboard view render: OK" . PHP_EOL;
    } catch (Exception $e) {
        echo "Dashboard view error: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test rendering the residents view
    try {
        $view = view('residents.index');
        echo "Residents view render: OK" . PHP_EOL;
    } catch (Exception $e) {
        echo "Residents view error: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test rendering the resident-reports view
    try {
        $view = view('resident-reports.index');
        echo "Resident-reports view render: OK" . PHP_EOL;
    } catch (Exception $e) {
        echo "Resident-reports view error: " . $e->getMessage() . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}
