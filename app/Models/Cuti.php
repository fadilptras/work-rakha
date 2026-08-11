<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuti extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_cuti',
        'tanggal_mulai',
        'tanggal_selesai',
        'total_hari',
        'alasan',
        'lampiran',
        'status',
        
        // Approver 1
        'approver_cuti_1_id', 'status_approver_1', 'catatan_approver_1', 'tanggal_approve_1',
        
        // Approver 2
        'approver_cuti_2_id', 'status_approver_2', 'catatan_approver_2', 'tanggal_approve_2',
        
        // Approver 3
        'approver_cuti_3_id', 'status_approver_3', 'catatan_approver_3', 'tanggal_approve_3',
        
        // Approver 4 (Ditambahkan)
        'approver_cuti_4_id', 'status_approver_4', 'catatan_approver_4', 'tanggal_approve_4',
    ];

    /**
     * Relasi ke model User (Pemohon).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke masing-masing peninjau (Approver).
     */
    public function approver1(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'approver_cuti_1_id');
    }

    public function approver2(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'approver_cuti_2_id');
    }

    public function approver3(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'approver_cuti_3_id');
    }

    public function approver4(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'approver_cuti_4_id');
    }

    /**
     * Accessor untuk nomor pengajuan dinamis
     */
    public function getNomorPengajuanAttribute()
    {
        $prefix = 'CUTI';
        $userId = str_pad($this->user_id, 3, '0', STR_PAD_LEFT);
        $tanggal = $this->created_at ? $this->created_at->format('dmY') : date('dmY');
        $urutan = str_pad($this->id, 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$userId}-{$tanggal}-{$urutan}";
    }

    /**
     * Accessor untuk nomor surat resmi (PDF)
     */
    public function getNomorSuratAttribute()
    {
        $bulanAngka = $this->created_at ? $this->created_at->format('m') : date('m');
        $tahunAngka = $this->created_at ? $this->created_at->format('Y') : date('Y');
        
        if ($this->id) {
            $urutanBulanan = self::whereYear('created_at', $tahunAngka)
                ->whereMonth('created_at', $bulanAngka)
                ->where('id', '<=', $this->id)
                ->count();
        } else {
            $urutanBulanan = self::whereYear('created_at', $tahunAngka)
                ->whereMonth('created_at', $bulanAngka)
                ->count() + 1;
        }

        $urutanPad = str_pad($urutanBulanan, 3, '0', STR_PAD_LEFT);
        $romawi = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        $bulan = $this->created_at ? $romawi[$this->created_at->format('n')] : $romawi[date('n')];
        $tahun = $this->created_at ? $this->created_at->format('Y') : date('Y');
        
        return "{$urutanPad}/RAKHA/{$bulan}/{$tahun}";
    }
}