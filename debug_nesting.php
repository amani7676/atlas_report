<?php

$content = file_get_contents('resources/views/livewire/residents/resident-reports.blade.php');
$lines = explode("\n", $content);

$stack = [];
$issues = [];

foreach ($lines as $lineNum => $line) {
    $trimmed = trim($line);
    if (empty($trimmed)) continue;
    
    // Skip comments and PHP
    if (str_starts_with($trimmed, '<!--') || str_starts_with($trimmed, '<?php')) {
        continue;
    }
    
    // Find opening tags
    if (preg_match_all('/<(\w+)(?:\s[^>]*)?(?<!\/)>/', $line, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $tag = $match[1];
            
            // Skip self-closing and special tags
            if (in_array($tag, ['link', 'img', 'input', 'br', 'hr', 'meta', 'style', 'script'])) {
                continue;
            }
            
            // Check if self-closing
            if (preg_match('/<\w+[^>]*\/>/', $match[0])) {
                continue;
            }
            
            $stack[] = ['tag' => $tag, 'line' => $lineNum + 1, 'depth' => count($stack)];
            
            // Check if this should be nested but isn't
            if (count($stack) > 1 && $stack[count($stack)-1]['depth'] === 0) {
                $issues[] = "Line " . ($lineNum + 1) . ": Tag <$tag> appears at root level but should be nested";
            }
        }
    }
    
    // Find closing tags
    if (preg_match_all('/<\/(\w+)>/', $line, $matches)) {
        foreach ($matches[1] as $tag) {
            if (!empty($stack)) {
                $opened = array_pop($stack);
                if ($opened['tag'] !== $tag) {
                    $issues[] = "Line " . ($lineNum + 1) . ": Mismatched tags. Expected </{$opened['tag']}>, got </$tag>";
                }
            } else {
                $issues[] = "Line " . ($lineNum + 1) . ": Extra closing tag </$tag>";
            }
        }
    }
}

echo "Nesting issues found:\n";
foreach ($issues as $issue) {
    echo "- $issue\n";
}

if (empty($issues)) {
    echo "✅ No nesting issues found\n";
} else {
    echo "❌ Found " . count($issues) . " nesting issues\n";
}

echo "\nFinal stack state: " . count($stack) . " unclosed tags\n";
foreach ($stack as $item) {
    echo "- <{$item['tag']}> from line {$item['line']}\n";
}
