<?php

namespace App\Models;

use App\Models\Concerns\SoftDeletesFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletesFlag;

    public const TYPES = [
        'RSUD',
        'RS Swasta',
        'RS TNI-Polri',
        'Klinik',
        'Puskesmas',
        'Apotek',
        'Lab',
        'Distributor',
        'Lainnya',
    ];

    protected $fillable = [
        'customer_name',
        'customer_type',
        'sales_customer_name',
        'phone',
        'email',
        'customer_address',
        'area',
        'anniversary_date',
        'npwp',
        'pharmacist_name',
        'pharmacist_license_no',
        'pharmacist_phone',
        'is_deleted',
    ];

    protected $casts = [
        'customer_address' => 'array',
        'anniversary_date' => 'date',
        'is_deleted' => 'boolean',
    ];

    /** Kontak person di bawah instansi ini. */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /** Interaksi seluruh kontak di bawah instansi ini. */
    public function interactionsThroughClients(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(ClientInteraction::class, Client::class, 'customer_id', 'client_id');
    }

    /** Alamat default: elemen ber-flag is_default, fallback elemen pertama. */
    public function defaultAddress(): ?array
    {
        $list = array_values(array_filter((array) ($this->customer_address ?? []), 'is_array'));
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
