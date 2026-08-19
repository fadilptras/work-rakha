<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Imports\SalesImport;
use Maatwebsite\Excel\Facades\Excel;

try {
    // Create a dummy CSV file to simulate Excel import
    $csvPath = __DIR__ . '/storage/app/dummy_sales.csv';
    $fp = fopen($csvPath, 'w');
    fputcsv($fp, ['tanggal', 'nama_customer', 'nama_produk', 'qty', 'satuan', 'hna', 'diskon', 'harga_nett', 'bulan', 'ps']);
    fputcsv($fp, ['2026-08-01', 'Test Customer', 'Test Product', '10', 'Pcs', '5000', '0', '50000', 'Agustus', 'PS1']);
    fclose($fp);
    
    echo "Importing...\n";
    Excel::import(new SalesImport, $csvPath);
    echo "Import Successful!\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
