<?php

$dir = __DIR__ . '/resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$files = [];

foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $files[] = $file->getPathname();
    }
}

$count = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;

    // Pattern to match @if(session('success')) ... @endif and error
    // Uses the 's' modifier so the dot matches newlines.
    // Reluctant quantifier .*? ensures it stops at the first @endif.
    $pattern = '/@if\s*\(\s*session\([\'"](success|error)[\'"]\)\s*\).*?@endif/s';

    $content = preg_replace($pattern, '', $content);
    
    // Also remove empty notification containers if they are left behind
    $content = preg_replace('/<div[^>]*class="[^"]*absolute top-20[^"]*"[^>]*>\s*@if\s*\(\$errors->any\(\)\).*?@endif\s*<\/div>/s', '', $content);
    // Actually wait, let's just leave the errors block if it exists, or remove the whole container if it's completely empty.
    $content = preg_replace('/<div[^>]*>\s*<\/div>/s', '', $content); // Basic empty div cleanup (might be dangerous, let's skip)

    if ($original !== $content) {
        file_put_contents($file, $content);
        echo "Cleaned: $file\n";
        $count++;
    }
}

echo "Total files cleaned: $count\n";
