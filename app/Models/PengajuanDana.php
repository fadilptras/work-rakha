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
        // isi form 
        'user_id',
        'judul_pengajuan',
        'divisi',
        'nama_bank',
        'no_rekening',
        'total_dana',
        'rincian_dana',
        'lampiran',
        'bukti_transfer',
        'status', // 'diajukan', 'diproses', 'disetujui', 'proses_pembayaran', 'selesai', 'ditolak', 'dibatalkan'
        'nama_rek',

        // approver 1
        'approver_dana_1_id',
        'approver_1_status', // 'menunggu', 'disetujui', 'ditolak', 'skipped'
        'approver_1_catatan',
        'approver_1_approved_at',
        
        // approver 2
        'approver_dana_2_id',
        'approver_2_status', // 'menunggu', 'disetujui', 'ditolak', 'skipped'
        'approver_2_catatan',
        'approver_2_approved_at',
        
        // approver 3
        'approver_dana_3_id',
        'approver_3_status',
        'approver_3_catatan',
        'approver_3_approved_at',

        // approver 4
        'approver_dana_4_id',
        'approver_4_status',
        'approver_4_catatan',
        'approver_4_approved_at',
        
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
    public function approverDana1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_dana_1_id');
    }

    /**
     * Relasi ke user Approver 2.
     * Dipanggil di Controller sebagai 'approver2'
     */
    public function approverDana2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_dana_2_id');
    }

    /**
     * Relasi ke user Approver 3.
     */
    public function approverDana3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_dana_3_id');
    }

    /**
     * Relasi ke user Approver 4.
     */
    public function approverDana4(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_dana_4_id');
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