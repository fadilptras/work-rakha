<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_pic_histories')) {
            Schema::create('client_pic_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('pic_name'); // Nama PIC (misal: "Bagus", "Indah", "Eko Sigit Nugroho")
                $table->date('start_date'); // Tanggal mulai
                $table->date('end_date')->nullable(); // Tanggal selesai (NULL jika masih aktif)
                $table->text('handover_notes')->nullable(); // Alasan/catatan handover
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['client_id', 'start_date', 'end_date']);
                $table->index('pic_name');
            });
        }

        // Backfill data awal untuk klien yang sudah ada agar tidak kehilangan riwayat aktif
        $clients = DB::table('clients')->get(['id', 'user_id', 'ps', 'created_at']);
        $now = now();
        $records = [];
        foreach ($clients as $c) {
            $picName = trim((string)($c->ps ?? ''));
            if ($picName === '' && !empty($c->user_id)) {
                $u = DB::table('users')->where('id', $c->user_id)->first(['name']);
                $picName = trim((string)($u->name ?? ''));
            }
            if ($picName !== '') {
                $exists = DB::table('client_pic_histories')->where('client_id', $c->id)->exists();
                if (!$exists) {
                    $startDate = !empty($c->created_at) ? substr((string)$c->created_at, 0, 10) : '2025-01-01';
                    $records[] = [
                        'client_id' => $c->id,
                        'user_id' => $c->user_id,
                        'pic_name' => $picName,
                        'start_date' => $startDate,
                        'end_date' => null,
                        'handover_notes' => 'Inisialisasi sistem riwayat PIC',
                        'created_by' => $c->user_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }
        if (!empty($records)) {
            DB::table('client_pic_histories')->insert($records);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_pic_histories');
    }
};

