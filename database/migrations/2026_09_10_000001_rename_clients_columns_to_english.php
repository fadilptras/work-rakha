<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Refactor tabel clients ke bahasa Inggris + soft-delete flag.
 *
 * Mapping:
 *  pic                 -> ps
 *  nama_user           -> contact_name
 *  no_telpon           -> contact_phone
 *  alamat_user         -> contact_address
 *  tanggal_lahir       -> contact_birth_date
 *  jabatan             -> contact_position
 *  hobby_client        -> contact_hobby
 *  nama_apoteker       -> pharmacist_name
 *  nomor_sipa          -> pharmacist_license_no
 *  no_telpon_apoteker  -> pharmacist_phone
 *  komisi              -> commission_rate
 *  nama_perusahaan     -> customer_name
 *  tanggal_berdiri     -> company_founded_date
 *  alamat_perusahaan   -> company_address
 *  bank                -> bank_name
 *  no_rekening         -> bank_account_number
 *  nama_di_rekening    -> bank_account_name
 *  saldo_awal          -> opening_balance
 *  + is_deleted TINYINT(1) DEFAULT 0 (pola SoftDeletesFlag seperti products)
 *
 * Idempotent: hanya rename bila kolom lama masih ada & kolom baru belum ada.
 */
return new class extends Migration
{
    private array $renames = [
        'pic'                => 'ps',
        'nama_user'          => 'contact_name',
        'no_telpon'          => 'contact_phone',
        'alamat_user'        => 'contact_address',
        'tanggal_lahir'      => 'contact_birth_date',
        'jabatan'            => 'contact_position',
        'hobby_client'       => 'contact_hobby',
        'nama_apoteker'      => 'pharmacist_name',
        'nomor_sipa'         => 'pharmacist_license_no',
        'no_telpon_apoteker' => 'pharmacist_phone',
        'komisi'             => 'commission_rate',
        'nama_perusahaan'    => 'customer_name',
        'tanggal_berdiri'    => 'company_founded_date',
        'alamat_perusahaan'  => 'company_address',
        'bank'               => 'bank_name',
        'no_rekening'        => 'bank_account_number',
        'nama_di_rekening'   => 'bank_account_name',
        'saldo_awal'         => 'opening_balance',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        foreach ($this->renames as $old => $new) {
            if (Schema::hasColumn('clients', $old) && ! Schema::hasColumn('clients', $new)) {
                Schema::table('clients', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        if (! Schema::hasColumn('clients', 'is_deleted')) {
            DB::statement('ALTER TABLE clients ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER commission_rate');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('clients')) {
            return;
        }

        if (Schema::hasColumn('clients', 'is_deleted')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }

        foreach (array_reverse($this->renames, true) as $old => $new) {
            if (Schema::hasColumn('clients', $new) && ! Schema::hasColumn('clients', $old)) {
                Schema::table('clients', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($new, $old);
                });
            }
        }
    }
};
