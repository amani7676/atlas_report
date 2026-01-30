<?php

// Test to check Livewire single root element requirement
$content = file_get_contents('resources/views/livewire/residents/resident-reports.blade.php');

// Remove comments and PHP tags for cleaner analysis
$content = preg_replace('/<!--.*?-->/s', '', $content);
$content = preg_replace('/<\?php.*?\?>/s', '', $content);

// Find all top-level elements (not nested inside other tags)
$lines = explode("\n", $content);
$topLevelElements = [];
$depth = 0;

foreach ($lines as $lineNum => $line) {
    $trimmed = trim($line);
    if (empty($trimmed)) continue;
    
    // Count opening and closing tags to track depth
    preg_match_all('/<(\w+)[^>]*>/', $trimmed, $openingMatches);
    preg_match_all('/<\/(\w+)>/', $trimmed, $closingMatches);
    
    foreach ($openingMatches[1] as $tag) {
        if ($depth === 0 && !in_array($tag, ['link', 'style', 'script', 'meta'])) {
            $topLevelElements[] = ['tag' => $tag, 'line' => $lineNum + 1];
        }
        if (!in_array($tag, ['link', 'img', 'input', 'br', 'hr', 'meta'])) {
            $depth++;
        }
    }
    
    foreach ($closingMatches[1] as $tag) {
        if (!in_array($tag, ['link', 'img', 'input', 'br', 'hr', 'meta'])) {
            $depth--;
        }
    }
}

echo "Top-level elements found:\n";
foreach ($topLevelElements as $element) {
    echo "- {$element['tag']} (line {$element['line']})\n";
}

echo "\nTotal top-level elements: " . count($topLevelElements) . "\n";

if (count($topLevelElements) === 1 && $topLevelElements[0]['tag'] === 'div') {
    echo "✅ SUCCESS: Livewire component has exactly one root element\n";
} else {
    echo "❌ ERROR: Livewire component has multiple root elements\n";
    echo "Livewire requires exactly ONE root element, but found " . count($topLevelElements) . "\n";
}

// Check if file starts properly
$firstNonEmptyLine = '';
foreach ($lines as $line) {
    if (!empty(trim($line))) {
        $firstNonEmptyLine = trim($line);
        break;
    }
}

echo "\nFirst non-empty line: $firstNonEmptyLine\n";
if (str_starts_with($firstNonEmptyLine, '<div>')) {
    echo "✅ File starts with div tag\n";
} else {
    echo "❌ File does not start with div tag\n";
}
