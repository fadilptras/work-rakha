<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPendidikan extends Model
{
    use HasFactory;
    
    // Tentukan nama tabel jika tidak jamak (opsional tapi aman)
    protected $table = 'riwayat_pendidikan';

    protected $fillable = [
        'user_id',
        'jenjang',
        'nama_institusi',
        'jurusan',
        'tahun_lulus',
        'file_ijazah',
    ];

    // Kita tidak perlu relasi baliknya (belongsTo) untuk form ini
}