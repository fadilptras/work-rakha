<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom data pribadi, keuangan, dan kontrak yang belum ada di tabel users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Identitas dasar
            $table->string('nip', 100)->nullable()->unique()->after('id');
            $table->string('status_karyawan', 50)->nullable()->after('nip'); // Tetap/Kontrak/Percobaan

            // Kontak & lokasi kerja
            $table->string('lokasi_kerja', 100)->nullable()->after('divisi');
            $table->string('nomor_telepon')->nullable()->after('lokasi_kerja');

            // Alamat
            $table->text('alamat_ktp')->nullable()->comment('Diubah dari alamat')->after('nomor_telepon');
            $table->text('alamat_domisili')->nullable()->after('alamat_ktp');

            // Data personal
            $table->string('tempat_lahir')->nullable()->after('alamat_domisili');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('jenis_kelamin')->nullable()->after('tanggal_lahir');
            $table->string('agama', 50)->nullable()->after('jenis_kelamin');
            $table->string('golongan_darah', 10)->nullable()->after('agama');
            $table->string('status_pernikahan', 50)->nullable()->after('golongan_darah');

            // Identitas kependudukan
            $table->string('nik')->nullable()->after('status_pernikahan');
            $table->string('file_ktp')->nullable()->after('nik');

            // Kontak darurat
            $table->string('kontak_darurat_nama')->nullable()->after('file_ktp');
            $table->string('kontak_darurat_nomor')->nullable()->after('kontak_darurat_nama');
            $table->string('kontak_darurat_hubungan', 100)->nullable()->after('kontak_darurat_nomor');

            // Tanggal kontrak
            $table->date('tanggal_mulai_kontrak')->nullable()->after('tanggal_bergabung');
            $table->date('tanggal_akhir_kontrak')->nullable()->after('tanggal_mulai_kontrak');
            $table->date('tanggal_berhenti')->nullable()->after('tanggal_akhir_kontrak');

            // NPWP
            $table->string('npwp', 100)->nullable()->after('tanggal_berhenti');
            $table->string('file_npwp')->nullable()->after('npwp');
            $table->string('ptkp', 20)->nullable()->after('file_npwp');

            // BPJS
            $table->string('bpjs_kesehatan', 100)->nullable()->after('ptkp');
            $table->string('file_bpjs_kesehatan')->nullable()->after('bpjs_kesehatan');
            $table->string('bpjs_ketenagakerjaan', 100)->nullable()->after('file_bpjs_kesehatan');
            $table->string('file_bpjs_ketenagakerjaan')->nullable()->after('bpjs_ketenagakerjaan');

            // Bank
            $table->string('nama_bank', 100)->nullable()->after('file_bpjs_ketenagakerjaan');
            $table->string('nomor_rekening', 100)->nullable()->after('nama_bank');
            $table->string('pemilik_rekening')->nullable()->after('nomor_rekening');

            // Sisa cuti
            $table->integer('sisa_cuti')->nullable()->default(0)->after('jatah_cuti');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nip',
                'status_karyawan',
                'lokasi_kerja',
                'nomor_telepon',
                'alamat_ktp',
                'alamat_domisili',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'agama',
                'golongan_darah',
                'status_pernikahan',
                'nik',
                'file_ktp',
                'kontak_darurat_nama',
                'kontak_darurat_nomor',
                'kontak_darurat_hubungan',
                'tanggal_mulai_kontrak',
                'tanggal_akhir_kontrak',
                'tanggal_berhenti',
                'npwp',
                'file_npwp',
                'ptkp',
                'bpjs_kesehatan',
                'file_bpjs_kesehatan',
                'bpjs_ketenagakerjaan',
                'file_bpjs_ketenagakerjaan',
                'nama_bank',
                'nomor_rekening',
                'pemilik_rekening',
                'sisa_cuti',
            ]);
        });
    }
};
