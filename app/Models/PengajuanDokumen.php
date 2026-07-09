<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanDokumen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_dokumen',
        'deskripsi',
        'file_pendukung',
        'status',
        'catatan_admin',
        'file_hasil',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}