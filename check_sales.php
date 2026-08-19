<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sales;
use Illuminate\Support\Facades\DB;

$sales = Sales::whereYear('tanggal', 2026)
    ->select('bulan', DB::raw('SUM(harga_nett) as total'), DB::raw('COUNT(*) as count'))
    ->groupBy('bulan')
    ->get();

print_r($sales->toArray());

// Also fetch a few records from July to see the raw data
$july = Sales::whereMonth('tanggal', 7)->whereYear('tanggal', 2026)->limit(5)->get();
echo "\n--- Raw July Data ---\n";
foreach($july as $row) {
    echo "ID: {$row->id}, Tanggal: {$row->tanggal}, Bulan: {$row->bulan}, Nett: {$row->harga_nett}\n";
}
