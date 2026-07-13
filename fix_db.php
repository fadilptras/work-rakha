<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('users', 'approver_dana_3_id')) {
    Schema::table('users', function (Blueprint $table) {
        $table->unsignedBigInteger('approver_dana_3_id')->nullable()->after('approver_2_id');
        $table->unsignedBigInteger('approver_dana_4_id')->nullable()->after('approver_dana_3_id');
    });
    echo "Columns added to users.\n";
} else {
    echo "Columns already exist in users.\n";
}

if (!Schema::hasColumn('pengajuan_dana', 'approver_3_id')) {
    Schema::table('pengajuan_dana', function (Blueprint $table) {
        $table->unsignedBigInteger('approver_3_id')->nullable()->after('approver_2_approved_at');
        $table->string('approver_3_status')->default('menunggu')->after('approver_3_id');
        $table->text('approver_3_catatan')->nullable()->after('approver_3_status');
        $table->timestamp('approver_3_approved_at')->nullable()->after('approver_3_catatan');

        $table->unsignedBigInteger('approver_4_id')->nullable()->after('approver_3_approved_at');
        $table->string('approver_4_status')->default('menunggu')->after('approver_4_id');
        $table->text('approver_4_catatan')->nullable()->after('approver_4_status');
        $table->timestamp('approver_4_approved_at')->nullable()->after('approver_4_catatan');
    });
    echo "Columns added to pengajuan_dana.\n";
} else {
    echo "Columns already exist in pengajuan_dana.\n";
}
