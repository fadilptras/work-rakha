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
        'user_id', 'judul_pengajuan', 'divisi', 'catatan_pemohon', 'rincian_barang', 'lampiran',
        'status', 'status_monitoring', 'riwayat_monitoring', 'data_termin',
        'approver_barang_1_id', 'status_appr_1', 'catatan_approver_1', 'tanggal_approved_1',
        'approver_barang_2_id', 'status_appr_2', 'catatan_approver_2', 'tanggal_approved_2',
        'approver_barang_3_id', 'status_appr_3', 'catatan_approver_3', 'tanggal_approved_3',
        'approver_barang_4_id', 'status_appr_4', 'catatan_approver_4', 'tanggal_approved_4',
    ];

    protected $casts = [
        'rincian_barang' => 'array',
        'lampiran' => 'array',
        'riwayat_monitoring' => 'array',
        'data_termin' => 'array',
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
