<?php
$files = [
    'resources/views/admin/kpi/evaluate.blade.php',
    'resources/views/users/kpi/kpi-form-marketing.blade.php',
    'resources/views/users/kpi/kpi-form-umum.blade.php'
];

foreach ($files as $file) {
    if(!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // For evaluate and marketing form only
    if (strpos($file, 'kpi-form-umum') === false) {
        $content = str_replace(
            '<td class="p-3 text-center text-sm font-bold text-slate-700 bg-slate-50">
                                                    {{ $indicator->target ?? \'-\' }}
                                                </td>',
            '<td class="p-3 text-center">
                                                    <input type="text" name="target_values[{{ $indicator->id }}]" 
                                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm p-2 text-center font-bold text-slate-700" 
                                                        placeholder="Input Target"
                                                        value="{{ $item ? $item->target_value : ($indicator->target ?? \'\') }}"
                                                        {{ ($evaluation && in_array($evaluation->status, [\'disetujui_direktur\', \'acknowledged\'])) ? \'readonly\' : \'required\' }}>
                                                </td>',
            $content
        );
    }
    
    // Convert old status check to new one in all files
    $content = str_replace(
        "\$evaluation->status == 'approved'",
        "in_array(\$evaluation->status, ['disetujui_direktur', 'acknowledged'])",
        $content
    );
    $content = str_replace(
        "\$evaluation->status != 'approved'",
        "!in_array(\$evaluation->status, ['disetujui_direktur', 'acknowledged'])",
        $content
    );
    
    file_put_contents($file, $content);
    echo $file . ' updated.' . PHP_EOL;
}
