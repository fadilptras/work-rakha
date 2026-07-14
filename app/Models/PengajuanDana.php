<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanDana extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_dana';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'judul_pengajuan',
        'divisi',
        'nama_bank',
        'no_rekening',
        'total_dana',
        'rincian_dana',
        'lampiran',
        'status', // 'diajukan', 'diproses_appr_2', 'proses_pembayaran', 'selesai', 'ditolak', 'dibatalkan'
        'nama_rek',

        // Kolom untuk Approver 1 (dari User)
        'approver_1_id',
        'approver_1_status', // 'menunggu', 'disetujui', 'ditolak', 'skipped'
        'approver_1_catatan',
        'approver_1_approved_at',
        
        // Kolom untuk Approver 2 (dari User)
        'approver_2_id',
        'approver_2_status', // 'menunggu', 'disetujui', 'ditolak', 'skipped'
        'approver_2_catatan',
        'approver_2_approved_at',
        
        // Kolom untuk Approver 3 (dari User) - Bagian ini yang upload bukti transfer
        'approver_3_id',
        'approver_3_status',
        'approver_3_catatan',
        'approver_3_approved_at',

        // Kolom untuk Approver 4 (dari User) - Verifikasi akhir (Admin)
        'approver_4_id',
        'approver_4_status',
        'approver_4_catatan',
        'approver_4_approved_at',
        
        // Kolom untuk Finance (Manager Keuangan dicek dinamis)
        'payment_status', // 'menunggu', 'diproses', 'selesai', 'ditolak', 'skipped'
        'catatan_finance',
        'finance_id', // Siapa (Auth::id()) finance yg memproses/upload
        'finance_processed_at',

        'invoice',
        'bukti_transfer',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rincian_dana' => 'array',
        'lampiran' => 'array',
        'approver_1_approved_at' => 'datetime',
        'approver_2_approved_at' => 'datetime',
        'approver_3_approved_at' => 'datetime',
        'approver_4_approved_at' => 'datetime',
        'finance_processed_at' => 'datetime',
    ];

    /**
     * Relasi ke pemohon.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke user Approver 1.
     * Dipanggil di Controller sebagai 'approver1'
     */
    public function approver1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_1_id');
    }

    /**
     * Relasi ke user Approver 2.
     * Dipanggil di Controller sebagai 'approver2'
     */
    public function approver2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_2_id');
    }

    /**
     * Relasi ke user Approver 3.
     */
    public function approver3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_3_id');
    }

    /**
     * Relasi ke user Approver 4.
     */
    public function approver4(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_4_id');
    }

    /**
     * Relasi ke user Finance yang memproses (upload bukti transfer).
     * Dipanggil di Controller sebagai 'financeProcessor'
     */
    public function financeProcessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_id');
    }

    /**
     * Accessor untuk nomor pengajuan dinamis
     */
    public function getNomorPengajuanAttribute()
    {
        $prefix = 'DANA';
        $userId = str_pad($this->user_id, 3, '0', STR_PAD_LEFT);
        $tanggal = $this->created_at ? $this->created_at->format('dmY') : date('dmY');
        $urutan = str_pad($this->id, 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$userId}-{$tanggal}-{$urutan}";
    }
}