<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'start_time',
        'end_time',
        'description',
        'location',
        'color',
    ];

    // Relasi ke User yang membuat agenda
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke para tamu (User) yang diundang
    public function guests()
    {
        return $this->belongsToMany(User::class, 'agenda_user', 'agenda_id', 'user_id');
    }

    
}