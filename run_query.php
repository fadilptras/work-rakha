<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $barangsCount = DB::table('barangs')->count();
    $logsCount = DB::table('stock_logs')->count();
    $historyCount = DB::table('daily_stock_histories')->count();
    
    echo "Barangs Count: $barangsCount\n";
    echo "Stock Logs Count: $logsCount\n";
    echo "Daily Stock Histories Count: $historyCount\n";
    
    $samples = DB::table('barangs')->where('stok', '>', 0)->take(3)->get();
    echo "Sample Barangs with stock:\n";
    print_r($samples);
    
    $latestLog = DB::table('stock_logs')->latest()->first();
    echo "Latest Stock Log:\n";
    print_r($latestLog);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
