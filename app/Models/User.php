<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\PengajuanDana;
use App\Models\RiwayatPendidikan;
use App\Models\RiwayatPekerjaan;
use App\Models\PengajuanBarang;
use App\Models\Cuti;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\Agenda;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'fcm_token',
        'profile_picture',
        'jabatan',
        'tanggal_bergabung',
        'divisi',
        'is_kepala_divisi',

        // Approver pengajuan dana
        'approver_1_id',
        'approver_2_id',
        'approver_dana_3_id',
        'approver_dana_4_id',
        'manager_keuangan_id',

        // approver pengajuan barang
        'approver_barang_1_id', 'approver_barang_2_id', 'approver_barang_3_id', 'approver_barang_4_id',

        // approver pengajuan cuti (Ditambahkan approver_cuti_4_id)
        'approver_cuti_1_id', 'approver_cuti_2_id', 'approver_cuti_3_id', 'approver_cuti_4_id',

        // Informasi Pribadi
        'nomor_telepon',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'nik',
        'kontak_darurat_nama',
        'kontak_darurat_nomor',
        'nip',
        'status_karyawan',
        'atasan_id',
        'lokasi_kerja',
        'tanggal_mulai_kontrak',
        'tanggal_akhir_kontrak',
        'tanggal_berhenti',
        'agama',
        'golongan_darah',
        'status_pernikahan',
        'alamat_ktp',
        'alamat_domisili',
        'kontak_darurat_hubungan',
        'npwp',
        'file_npwp',
        'file_ktp',
        'ptkp',
        'bpjs_kesehatan',
        'file_bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'file_bpjs_ketenagakerjaan',

         // Informasi Bank
        'nama_bank',
        'nomor_rekening',
        'pemilik_rekening',
        
        'jatah_cuti',
        'sisa_cuti',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'tanggal_bergabung' => 'date:Y-m-d',
            'tanggal_lahir'     => 'date:Y-m-d',
            'tanggal_mulai_kontrak' => 'date:Y-m-d',
            'tanggal_akhir_kontrak' => 'date:Y-m-d',
            'tanggal_berhenti'      => 'date:Y-m-d',
        ];
    }
    
    public function approver1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_1_id');
    }

    public function approver2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_2_id');
    }

    public function approverDana3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_dana_3_id');
    }

    public function approverDana4(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_dana_4_id');
    }

    public function managerKeuangan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_keuangan_id');
    }

    public function pengajuanDanas(): HasMany
    {
        return $this->hasMany(PengajuanDana::class, 'user_id');
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function cutis(): HasMany
    {
        return $this->hasMany(Cuti::class);
    }

    public function lemburs(): HasMany
    {
        return $this->hasMany(Lembur::class);
    }

    public function invitedAgendas()
    {
        return $this->belongsToMany(Agenda::class, 'agenda_user', 'user_id', 'agenda_id');
    }



    public function riwayatPendidikan(): HasMany
    {
        return $this->hasMany(RiwayatPendidikan::class, 'user_id');
    }

    public function riwayatPekerjaan(): HasMany
    {
        return $this->hasMany(RiwayatPekerjaan::class, 'user_id');
    }

    public function pengajuanBarangs(): HasMany
    {
        return $this->hasMany(PengajuanBarang::class);
    }

    // Relasi untuk Approver Cuti (Ditambahkan approverCuti4)
    public function approverCuti1(): BelongsTo {
        return $this->belongsTo(User::class, 'approver_cuti_1_id');
    }

    public function approverCuti2(): BelongsTo {
        return $this->belongsTo(User::class, 'approver_cuti_2_id');
    }

    public function approverCuti3(): BelongsTo {
        return $this->belongsTo(User::class, 'approver_cuti_3_id');
    }

    public function approverCuti4(): BelongsTo {
        return $this->belongsTo(User::class, 'approver_cuti_4_id');
    }

    // Relasi untuk Approver Barang
    public function approverBarang1(): BelongsTo {
        return $this->belongsTo(User::class, 'approver_barang_1_id');
    }

    public function approverBarang2(): BelongsTo {
        return $this->belongsTo(User::class, 'approver_barang_2_id');
    }

    public function approverBarang3(): BelongsTo {
        return $this->belongsTo(User::class, 'approver_barang_3_id');
    }
    
    public function approverBarang4(): BelongsTo {
        return $this->belongsTo(User::class, 'approver_barang_4_id');
    }
}