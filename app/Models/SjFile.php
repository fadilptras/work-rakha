<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SjFile extends Model
{
    protected $fillable = [
        'ws_mutation_id',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(WsMutation::class, 'ws_mutation_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
