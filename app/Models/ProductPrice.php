<?php

namespace App\Models;

use App\Models\Concerns\SoftDeletesFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory, SoftDeletesFlag;

    protected $fillable = [
        'product_name',
        'presentation',
        'pack_qty',
        'base_price',
        'unit_price',
        'is_deleted',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'pack_qty' => 'integer',
        'is_deleted' => 'boolean',
    ];
}