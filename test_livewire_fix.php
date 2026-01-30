<?php

// Simple test to check if the Livewire component has valid structure
require_once 'vendor/autoload.php';

// Read the blade file
$content = file_get_contents('resources/views/livewire/residents/resident-reports.blade.php');

// Check for multiple root elements
$lines = explode("\n", $content);
$rootElements = [];
$inTag = false;
$currentTag = '';

foreach ($lines as $line) {
    $trimmed = trim($line);
    
    // Skip empty lines and comments
    if (empty($trimmed) || str_starts_with($trimmed, '<!--') || str_starts_with($trimmed, '<?php')) {
        continue;
    }
    
    // Check for opening tags
    if (preg_match('/^<(\w+)/', $trimmed, $matches)) {
        $tag = $matches[1];
        if (!in_array($tag, ['link', 'style', 'script', 'meta'])) {
            $rootElements[] = $tag;
        }
    }
}

echo "Root elements found: " . implode(', ', $rootElements) . "\n";
echo "Number of root elements: " . count($rootElements) . "\n";

if (count($rootElements) === 1 && $rootElements[0] === 'div') {
    echo "✅ SUCCESS: Component has exactly one root element (div)\n";
} else {
    echo "❌ ERROR: Component has multiple root elements or incorrect structure\n";
}

// Check if the file starts with a div
if (str_starts_with(trim($content), '<div>')) {
    echo "✅ SUCCESS: File starts with a div tag\n";
} else {
    echo "❌ ERROR: File does not start with a div tag\n";
}

// Check if the file ends with a closing div
if (str_ends_with(trim($content), '</div>')) {
    echo "✅ SUCCESS: File ends with a closing div tag\n";
} else {
    echo "❌ ERROR: File does not end with a closing div tag\n";
}
