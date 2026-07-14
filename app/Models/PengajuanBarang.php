<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanBarang extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_barang';
    protected $fillable = [
        'user_id', 'judul_pengajuan', 'divisi', 'rincian_barang', 'lampiran',
        'status', 
        'approver_barang_1_id', 'status_appr_1', 'catatan_approver_1', 'tanggal_approved_1',
        'approver_barang_2_id', 'status_appr_2', 'catatan_approver_2', 'tanggal_approved_2',
        'approver_barang_3_id', 'status_appr_3', 'catatan_approver_3', 'tanggal_approved_3',
        'approver_barang_4_id', 'status_appr_4', 'catatan_approver_4', 'tanggal_approved_4',
    ];

    protected $casts = [
        'rincian_barang' => 'array',
        'lampiran' => 'array',
        'tanggal_approved_1' => 'datetime', 
        'tanggal_approved_2' => 'datetime',
        'tanggal_approved_3' => 'datetime',
        'tanggal_approved_4' => 'datetime',
    ];


    /**
     * Relasi ke pembuat pengajuan (User)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver1() {
        return $this->belongsTo(User::class, 'approver_barang_1_id');
    }

    public function approver2() {
        return $this->belongsTo(User::class, 'approver_barang_2_id');
    }

    public function approver3() {
        return $this->belongsTo(User::class, 'approver_barang_3_id');
    }
    
    public function approver4() {
        return $this->belongsTo(User::class, 'approver_barang_4_id');
    }

    /**
     * Accessor untuk nomor pengajuan dinamis
     */
    public function getNomorPengajuanAttribute()
    {
        $prefix = 'BRG';
        $userId = str_pad($this->user_id, 3, '0', STR_PAD_LEFT);
        $tanggal = $this->created_at ? $this->created_at->format('dmY') : date('dmY');
        $urutan = str_pad($this->id, 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$userId}-{$tanggal}-{$urutan}";
    }
}
