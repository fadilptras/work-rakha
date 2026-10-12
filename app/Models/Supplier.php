<?php

namespace App\Models;

use App\Models\Concerns\SoftDeletesFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, SoftDeletesFlag;

    protected $table = 'suppliers';

    public const TYPES = [
        'Pabrik',
        'Distributor',
        'Agen',
        'Importir',
        'Lainnya',
    ];

    protected $fillable = [
        'supplier_name',
        'supplier_type',
        'phone',
        'email',
        'supplier_address',
        'pic_name',
        'pic_phone',
        'npwp',
        'termin_days',
        'is_deleted',
    ];

    protected $casts = [
        'supplier_address' => 'array',
        'is_deleted' => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(SupplierPrice::class);
    }

    /** Alamat default: elemen ber-flag is_default, fallback elemen pertama. */
    public function defaultAddress(): ?array
    {
        $list = array_values(array_filter((array) ($this->supplier_address ?? []), 'is_array'));
        if (empty($list)) return null;
        foreach ($list as $addr) {
            if (!empty($addr['is_default'])) return $addr;
        }

        return $list[0];
    }

    /** Teks alamat default untuk tampilan ('' bila tidak ada). */
    public function displayAddress(): string
    {
        $d = $this->defaultAddress();
        if (!$d) return '';
        $addr = trim((string) ($d['address'] ?? ''));
        $city = trim((string) ($d['city'] ?? ''));

        return trim($addr . ($city !== '' ? ', ' . $city : ''));
    }
}
