<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aktivitas extends Model
{
    use HasFactory;

    /**
     * Tentukan nama tabel jika nama model Anda tidak jamak (bukan 'Aktivitas' -> 'aktivitas').
     */
    protected $table = 'aktivitas';

    /**
     * Kolom yang boleh diisi secara massal (mass assignment).
     */
    protected $fillable = [
        'user_id',
        'title',
        'keterangan',
        'lampiran',
        'latitude',
        'longitude',
    ];

    /**
     * Definisikan relasi bahwa setiap aktivitas 'dimiliki oleh' satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}