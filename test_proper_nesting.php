<?php

// Better test to check Livewire single root element with proper nesting detection
$content = file_get_contents('resources/views/livewire/residents/resident-reports.blade.php');

// Remove comments and PHP tags for cleaner analysis
$content = preg_replace('/<!--.*?-->/s', '', $content);
$content = preg_replace('/<\?php.*?\?>/s', '', $content);

$lines = explode("\n", $content);
$stack = [];
$rootElements = [];
$currentDepth = 0;

foreach ($lines as $lineNum => $line) {
    $trimmed = trim($line);
    if (empty($trimmed)) continue;
    
    // Find all opening tags
    if (preg_match_all('/<(\w+)(?:\s[^>]*)?(?<!\/)>/', $line, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $tag = $match[1];
            
            // Skip self-closing tags and style/link/script/meta
            if (in_array($tag, ['link', 'img', 'input', 'br', 'hr', 'meta', 'style', 'script'])) {
                continue;
            }
            
            // Check if this is a self-closing tag (ends with />)
            if (preg_match('/<\w+[^>]*\/>/', $match[0])) {
                continue;
            }
            
            if ($currentDepth === 0) {
                $rootElements[] = ['tag' => $tag, 'line' => $lineNum + 1, 'content' => $trimmed];
            }
            
            $stack[] = $tag;
            $currentDepth++;
        }
    }
    
    // Find all closing tags
    if (preg_match_all('/<\/(\w+)>/', $line, $matches)) {
        foreach ($matches[1] as $tag) {
            if (!empty($stack)) {
                $lastTag = array_pop($stack);
                $currentDepth--;
            }
        }
    }
}

echo "Root elements found:\n";
foreach ($rootElements as $element) {
    echo "- {$element['tag']} (line {$element['line']})\n";
    echo "  Content: {$element['content']}\n";
}

echo "\nTotal root elements: " . count($rootElements) . "\n";

if (count($rootElements) === 1 && $rootElements[0]['tag'] === 'div') {
    echo "✅ SUCCESS: Livewire component has exactly one root element\n";
} else {
    echo "❌ ERROR: Livewire component has multiple root elements\n";
    echo "Livewire requires exactly ONE root element, but found " . count($rootElements) . "\n";
}

// Check file structure
$firstLine = '';
$lastLine = '';
foreach ($lines as $line) {
    if (!empty(trim($line))) {
        $firstLine = trim($line);
        break;
    }
}

for ($i = count($lines) - 1; $i >= 0; $i--) {
    if (!empty(trim($lines[$i]))) {
        $lastLine = trim($lines[$i]);
        break;
    }
}

echo "\nFirst line: $firstLine\n";
echo "Last line: $lastLine\n";

if (str_starts_with($firstLine, '<div>') && str_ends_with($lastLine, '</div>')) {
    echo "✅ File has proper div wrapper\n";
} else {
    echo "❌ File does not have proper div wrapper\n";
}
