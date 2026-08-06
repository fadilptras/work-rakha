<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $fillable = [
        'nama_supplier', 
        'pic_1', 
        'pic_2', 
        'kontak_pic1', 
        'kontak_pic2', 
        'alamat'
    ];
}
