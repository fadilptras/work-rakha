<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
DB::statement("ALTER TABLE pengajuan_barang MODIFY COLUMN status ENUM('diajukan', 'diproses', 'disetujui', 'selesai', 'ditolak', 'dibatalkan', 'proses_finalisasi') DEFAULT 'diajukan'");
echo "Success\n";
