<?php
$file = 'resources/views/users/kpi/kpi-pdf-marketing.blade.php';
$content = file_get_contents($file);
$content = str_replace(
    '<body>',
    "<body>\n\n    @include('pdf.partials.kop-surat')",
    $content
);
$content = str_replace(
    '<td class="text-center">{{ $item->indicator->target }}</td>',
    '<td class="text-center">{{ $item->target_value ?? $item->indicator->target }}</td>',
    $content
);
file_put_contents($file, $content);
echo "Marketing PDF updated.\n";

$file2 = 'resources/views/users/kpi/kpi-pdf-umum.blade.php';
$content2 = file_get_contents($file2);
$content2 = str_replace(
    '<body>',
    "<body>\n\n    @include('pdf.partials.kop-surat')",
    $content2
);
file_put_contents($file2, $content2);
echo "Umum PDF updated.\n";
