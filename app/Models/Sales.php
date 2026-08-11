<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'sales';
    protected $fillable = [
        'tanggal', 
        'nama_customer', 
        'nama_produk', 
        'qty', 
        'satuan', 
        'hna', 
        'diskon', 
        'harga_nett', 
        'bulan', 
        'ps'
    ];
}