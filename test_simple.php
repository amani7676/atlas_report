<?php

echo "=== Simple Test ===" . PHP_EOL;

// Check if files exist
$files = [
    'public/index.php',
    'app/Http/Controllers/ResidentController.php',
    'resources/views/residents/index.blade.php',
    'resources/views/layouts/app.blade.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "$file: EXISTS" . PHP_EOL;
    } else {
        echo "$file: NOT FOUND" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Checking PHP Errors ===" . PHP_EOL;

// Check for syntax errors in key files
$phpFiles = [
    'app/Http/Controllers/ResidentController.php',
    'app/Http/Controllers/DashboardController.php',
    'app/Http/Controllers/ResidentReportController.php',
];

foreach ($phpFiles as $file) {
    $output = [];
    $returnCode = 0;
    exec("php -l \"$file\" 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "$file: SYNTAX OK" . PHP_EOL;
    } else {
        echo "$file: SYNTAX ERROR - " . implode(', ', $output) . PHP_EOL;
    }
}

echo PHP_EOL . "=== Checking Routes File ===" . PHP_EOL;

if (file_exists('routes/web.php')) {
    $output = [];
    $returnCode = 0;
    exec("php -l routes/web.php 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "routes/web.php: SYNTAX OK" . PHP_EOL;
    } else {
        echo "routes/web.php: SYNTAX ERROR - " . implode(', ', $output) . PHP_EOL;
    }
} else {
    echo "routes/web.php: NOT FOUND" . PHP_EOL;
}
