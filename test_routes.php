<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Testing Routes ===" . PHP_EOL;

try {
    $routes = app('router')->getRoutes();
    
    echo "All routes containing 'residents':" . PHP_EOL;
    foreach($routes as $route) {
        if(str_contains($route->uri(), 'residents')) {
            echo $route->uri() . ' -> ' . json_encode($route->getAction()) . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Testing Controllers ===" . PHP_EOL;
    
    // Test ResidentController
    try {
        $controller = new \App\Http\Controllers\ResidentController();
        echo "ResidentController: OK" . PHP_EOL;
    } catch (Exception $e) {
        echo "ResidentController Error: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test DashboardController
    try {
        $controller = new \App\Http\Controllers\DashboardController();
        echo "DashboardController: OK" . PHP_EOL;
    } catch (Exception $e) {
        echo "DashboardController Error: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test ResidentReportController
    try {
        $controller = new \App\Http\Controllers\ResidentReportController();
        echo "ResidentReportController: OK" . PHP_EOL;
    } catch (Exception $e) {
        echo "ResidentReportController Error: " . $e->getMessage() . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Testing Views ===" . PHP_EOL;
    
    // Test views
    $views = [
        'residents.index',
        'dashboard',
        'resident-reports.index'
    ];
    
    foreach ($views as $view) {
        if (view()->exists($view)) {
            echo "$view: EXISTS" . PHP_EOL;
        } else {
            echo "$view: NOT FOUND" . PHP_EOL;
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}
