<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Testing Controller Methods ===" . PHP_EOL;

try {
    // Test ResidentController index method
    $controller = new \App\Http\Controllers\ResidentController();
    
    echo "Testing ResidentController@index..." . PHP_EOL;
    $response = $controller->index();
    echo "Response type: " . get_class($response) . PHP_EOL;
    
    if ($response instanceof \Illuminate\View\View) {
        echo "View name: " . $response->getName() . PHP_EOL;
        echo "View data: " . json_encode($response->getData()) . PHP_EOL;
    }
    
    echo PHP_EOL . "Testing DashboardController@index..." . PHP_EOL;
    $dashboardController = new \App\Http\Controllers\DashboardController();
    $dashboardResponse = $dashboardController->index();
    echo "Dashboard response type: " . get_class($dashboardResponse) . PHP_EOL;
    
    if ($dashboardResponse instanceof \Illuminate\View\View) {
        echo "Dashboard view name: " . $dashboardResponse->getName() . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}
