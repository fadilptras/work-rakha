<?php
$files = [
    'resources/views/admin/kpi/evaluate.blade.php',
    'resources/views/users/kpi/kpi-form-marketing.blade.php'
];

foreach ($files as $file) {
    if(!file_exists($file)) continue;
    $content = file_get_contents($file);

    // Add padding to textareas
    $content = str_replace(
        'bg-slate-50 transition-colors duration-200" {',
        'bg-slate-50 transition-colors duration-200 p-4" {',
        $content
    );

    // Add dynamic unit to target input
    $targetInputOld = <<<EOD
<input type="text" name="target_values[{{ \$indicator->id }}]" 
                                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm p-2 text-center font-bold text-slate-700" 
                                                        placeholder="Input Target"
                                                        value="{{ \$item ? \$item->target_value : (\$indicator->target ?? '') }}"
                                                        {{ (\$evaluation && in_array(\$evaluation->status, ['disetujui_direktur', 'acknowledged'])) ? 'readonly' : 'required' }}>
EOD;

    $targetInputNew = <<<EOD
@php
                                                        \$unit = '';
                                                        if(stripos(\$indicator->name, 'Net Sales') !== false) \$unit = 'jt';
                                                        elseif(stripos(\$indicator->name, '% Grw') !== false || stripos(\$indicator->name, 'Rasio') !== false) \$unit = '%';
                                                        elseif(stripos(\$indicator->name, 'Basic Operation') !== false) \$unit = 'Outlet/hari';
                                                    @endphp
                                                    <div class="flex items-center gap-2">
                                                        <input type="text" name="target_values[{{ \$indicator->id }}]" 
                                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm p-2 text-center font-bold text-slate-700" 
                                                            placeholder="Target"
                                                            value="{{ \$item ? \$item->target_value : (\$indicator->target ?? '') }}"
                                                            {{ (\$evaluation && in_array(\$evaluation->status, ['disetujui_direktur', 'acknowledged'])) ? 'readonly' : 'required' }}>
                                                        @if(\$unit)
                                                            <span class="text-xs font-bold text-slate-500 w-16 text-left">{{ \$unit }}</span>
                                                        @endif
                                                    </div>
EOD;

    $content = str_replace($targetInputOld, $targetInputNew, $content);
    
    file_put_contents($file, $content);
    echo $file . " updated.\n";
}
