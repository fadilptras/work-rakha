<?php

// 1. Bersihkan sisa firebase di layout-users
$layoutPath = __DIR__ . '/resources/views/components/layout-users.blade.php';
$content = file_get_contents($layoutPath);
$content = preg_replace('/fetch\(".*?<\/script>/s', '</script>', $content);
$content = str_replace('<script>
            }

</script>', '', $content);
// let's just use regex to clean script before <x-toast />
$content = preg_replace('/<script>\s*\}\s*fetch\(.*?<\/script>/s', '', $content);
file_put_contents($layoutPath, $content);

// 2. Hapus inline flash messages dari semua file
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

    // Pattern to match @if(session('success')) ... @endif
    $pattern = '/@if\s*\(\s*session\([\'"](success|error)[\'"]\)\s*\).*?@endif/s';
    $content = preg_replace($pattern, '', $content);
    
    if ($original !== $content) {
        file_put_contents($file, $content);
        $count++;
    }
}

echo "Total files cleaned: $count\n";
