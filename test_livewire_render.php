<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Try to create the Livewire component
    $component = new \App\Livewire\Residents\ResidentReports();
    
    // Try to render the view
    $view = view('livewire.residents.resident-reports', [
        'component' => $component
    ]);
    
    echo "✅ Livewire component rendered successfully\n";
    echo "View length: " . strlen($view->render()) . " characters\n";
    
    // Check for multiple root elements in the rendered output
    $rendered = $view->render();
    
    // Simple check for multiple root divs
    preg_match_all('/<div[^>]*>/', $rendered, $matches);
    $divCount = count($matches[0]);
    
    echo "Number of div tags found: $divCount\n";
    
    if ($divCount > 0) {
        echo "✅ Component has div elements\n";
    } else {
        echo "❌ No div elements found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error rendering Livewire component:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if (str_contains($e->getMessage(), 'MultipleRootElementsDetectedException')) {
        echo "❌ Multiple root elements error confirmed\n";
    }
}
