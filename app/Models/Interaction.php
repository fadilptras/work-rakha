<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'nama_produk',
        'jenis_transaksi',
        'lokasi',
        'peserta',
        'nilai_kontribusi',
        'tanggal_interaksi',
        'catatan',
        'nilai_sales',
        'komisi',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}