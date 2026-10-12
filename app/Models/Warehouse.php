<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'address', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function stockMoves(): HasMany
    {
        return $this->hasMany(StockMove::class);
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->first() ?? static::first();
    }
}
