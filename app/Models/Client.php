<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'area',
        'pic',
        
        // Informasi Client
        'nama_user',
        'email',
        'no_telpon',
        'alamat_user', 
        'tanggal_lahir',
        'jabatan',
        'hobby_client',
        
        // Informasi Perusahaan
        'nama_perusahaan',
        'tanggal_berdiri',
        'alamat_perusahaan', 
        
        // Informasi Bank
        'bank',
        'no_rekening',
        'nama_di_rekening',
        'saldo_awal',
        
    ];

    protected $casts = [
        'tanggal_berdiri' => 'date',
        'tanggal_lahir' => 'date',
    ];

    /**
     * Relasi ke User (Sales Person) - TAMBAHKAN INI
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class)->orderBy('tanggal_interaksi', 'desc');
    }

    public function getTotalKontribusiAttribute()
    {
        if (!$this->relationLoaded('interactions')) {
            $this->load('interactions');
        }

        $pemasukan = $this->interactions
                          ->where('jenis_transaksi', 'IN') 
                          ->sum('nilai_kontribusi');
        
        $pengeluaran = $this->interactions
                            ->where('jenis_transaksi', 'OUT') 
                            ->sum('nilai_kontribusi');

        return $pemasukan - $pengeluaran;
    }
}