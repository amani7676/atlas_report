<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Testing HTTP Request Simulation ===" . PHP_EOL;

try {
    // Simulate a request to /residents
    $request = \Illuminate\Http\Request::create('/residents', 'GET');
    
    // Handle the request
    $response = $kernel->handle($request);
    
    echo "Request to /residents:" . PHP_EOL;
    echo "Status: " . $response->getStatusCode() . PHP_EOL;
    echo "Content type: " . $response->headers->get('Content-Type') . PHP_EOL;
    echo "Content length: " . strlen($response->getContent()) . PHP_EOL;
    
    // Check if it's an error response
    if ($response->getStatusCode() >= 400) {
        echo "Error content: " . substr($response->getContent(), 0, 500) . PHP_EOL;
    }
    
    echo PHP_EOL . "Testing /dashboard:" . PHP_EOL;
    
    $dashboardRequest = \Illuminate\Http\Request::create('/', 'GET');
    $dashboardResponse = $kernel->handle($dashboardRequest);
    
    echo "Dashboard status: " . $dashboardResponse->getStatusCode() . PHP_EOL;
    echo "Dashboard content length: " . strlen($dashboardResponse->getContent()) . PHP_EOL;
    
    if ($dashboardResponse->getStatusCode() >= 400) {
        echo "Dashboard error: " . substr($dashboardResponse->getContent(), 0, 500) . PHP_EOL;
    }
    
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}
