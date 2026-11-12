<?php

namespace App\Models;

use App\Models\Concerns\SoftDeletesFlag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Client extends Model
{
    use HasFactory, SoftDeletesFlag;

    protected $fillable = [
        'user_id',
        'customer_id',
        'ps',

        // Contact person (Modern)
        'client_name',
        'email',
        'phone',
        'address',
        'birthdate',
        'position',
        'hobby',

        // Contact person (Legacy DB fallback)
        'contact_phone',
        'contact_address',
        'contact_birth_date',
        'contact_position',
        'contact_hobby',

        // Instansi fields (Legacy clients table)
        'customer_name',
        'sales_customer_name',
        'area',
        'company_address',
        'company_founded_date',
        'customer_type',
        'company_phone',
        'company_email',
        'anniversary_date',
        'npwp',
        'pharmacist_name',
        'pharmacist_license_no',
        'pharmacist_phone',

        // Commission (per kontak)
        'commission_rate',

        // Bank
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'opening_balance',
        'is_deleted',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'is_deleted' => 'boolean',
    ];

    protected static ?array $cachedTableColumns = null;

    /**
     * Dapatkan daftar kolom fisik tabel clients (di-cache untuk efisiensi).
     */
    public static function getTableColumns(): array
    {
        if (static::$cachedTableColumns === null) {
            static::$cachedTableColumns = Schema::getColumnListing('clients');
        }

        return static::$cachedTableColumns;
    }

    /**
     * Cek apakah kolom customer_id sudah ada di tabel clients.
     */
    public static function hasCustomerIdColumn(): bool
    {
        return in_array('customer_id', static::getTableColumns(), true);
    }

    protected static function booted()
    {
        static::saving(function ($client) {
            $cols = static::getTableColumns();
            $hasPhone = in_array('phone', $cols, true);
            $hasContactPhone = in_array('contact_phone', $cols, true);

            // Jika di database masih skema kolom lama, sinkronkan field modern ke field fisik
            if (!$hasPhone && $hasContactPhone) {
                if (isset($client->attributes['phone']) && !isset($client->attributes['contact_phone'])) {
                    $client->attributes['contact_phone'] = $client->attributes['phone'];
                }
                if (isset($client->attributes['address']) && !isset($client->attributes['contact_address'])) {
                    $client->attributes['contact_address'] = $client->attributes['address'];
                }
                if (isset($client->attributes['birthdate']) && !isset($client->attributes['contact_birth_date'])) {
                    $client->attributes['contact_birth_date'] = $client->attributes['birthdate'];
                }
                if (isset($client->attributes['position']) && !isset($client->attributes['contact_position'])) {
                    $client->attributes['contact_position'] = $client->attributes['position'];
                }
                if (isset($client->attributes['hobby']) && !isset($client->attributes['contact_hobby'])) {
                    $client->attributes['contact_hobby'] = $client->attributes['hobby'];
                }
            } elseif ($hasPhone && !$hasContactPhone) {
                // Skema baru: sinkronkan field warisan ke field modern
                if (isset($client->attributes['contact_phone']) && !isset($client->attributes['phone'])) {
                    $client->attributes['phone'] = $client->attributes['contact_phone'];
                }
                if (isset($client->attributes['contact_address']) && !isset($client->attributes['address'])) {
                    $client->attributes['address'] = $client->attributes['contact_address'];
                }
                if (isset($client->attributes['contact_birth_date']) && !isset($client->attributes['birthdate'])) {
                    $client->attributes['birthdate'] = $client->attributes['contact_birth_date'];
                }
                if (isset($client->attributes['contact_position']) && !isset($client->attributes['position'])) {
                    $client->attributes['position'] = $client->attributes['contact_position'];
                }
                if (isset($client->attributes['contact_hobby']) && !isset($client->attributes['hobby'])) {
                    $client->attributes['hobby'] = $client->attributes['contact_hobby'];
                }
            }

            // Hapus atribut yang tidak ada di kolom database fisik agar tidak memicu 1054 Unknown column
            foreach (array_keys($client->attributes) as $attr) {
                if (!in_array($attr, $cols, true)) {
                    unset($client->attributes[$attr]);
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Accessor dinamis untuk customer:
     * Menjamin $client->customer tidak null pada database lama yang belum memiliki tabel relasi terpisah.
     */
    public function getCustomerAttribute()
    {
        if (array_key_exists('customer', $this->relations) && $this->relations['customer'] !== null) {
            return $this->relations['customer'];
        }

        if ($this->getAttributeFromArray('customer_id')) {
            $relation = $this->getRelationshipFromMethod('customer');
            if ($relation) {
                return $relation;
            }
        }

        // Fallback skema lama: instansiasi Customer virtual dari atribut clients yang ada
        $customerName = $this->attributes['customer_name'] ?? null;
        if (!empty($customerName)) {
            $virtual = new Customer([
                'customer_name' => $customerName,
                'sales_customer_name' => $this->attributes['sales_customer_name'] ?? null,
                'area' => $this->attributes['area'] ?? null,
                'pharmacist_name' => $this->attributes['pharmacist_name'] ?? null,
                'pharmacist_license_no' => $this->attributes['pharmacist_license_no'] ?? null,
                'pharmacist_phone' => $this->attributes['pharmacist_phone'] ?? null,
                'phone' => $this->attributes['company_phone'] ?? null,
                'email' => $this->attributes['company_email'] ?? null,
                'customer_type' => $this->attributes['customer_type'] ?? null,
                'npwp' => $this->attributes['npwp'] ?? null,
                'anniversary_date' => $this->attributes['anniversary_date'] ?? $this->attributes['company_founded_date'] ?? null,
            ]);

            if (!empty($this->attributes['company_address'])) {
                $virtual->customer_address = [
                    ['label' => 'Kantor', 'address' => $this->attributes['company_address'], 'city' => '', 'is_default' => true]
                ];
            }

            $this->setRelation('customer', $virtual);
            return $virtual;
        }

        return null;
    }

    // Accessor kompatibilitas ganda (phone / contact_phone)
    public function getPhoneAttribute()
    {
        return $this->attributes['phone'] ?? $this->attributes['contact_phone'] ?? null;
    }

    public function getContactPhoneAttribute()
    {
        return $this->attributes['contact_phone'] ?? $this->attributes['phone'] ?? null;
    }

    public function getAddressAttribute()
    {
        return $this->attributes['address'] ?? $this->attributes['contact_address'] ?? null;
    }

    public function getContactAddressAttribute()
    {
        return $this->attributes['contact_address'] ?? $this->attributes['address'] ?? null;
    }

    public function getBirthdateAttribute()
    {
        $val = $this->attributes['birthdate'] ?? $this->attributes['contact_birth_date'] ?? null;
        return $val ? Carbon::parse($val) : null;
    }

    public function getContactBirthDateAttribute()
    {
        $val = $this->attributes['contact_birth_date'] ?? $this->attributes['birthdate'] ?? null;
        return $val ? Carbon::parse($val) : null;
    }

    public function getPositionAttribute()
    {
        return $this->attributes['position'] ?? $this->attributes['contact_position'] ?? null;
    }

    public function getContactPositionAttribute()
    {
        return $this->attributes['contact_position'] ?? $this->attributes['position'] ?? null;
    }

    public function getHobbyAttribute()
    {
        return $this->attributes['hobby'] ?? $this->attributes['contact_hobby'] ?? null;
    }

    public function getContactHobbyAttribute()
    {
        return $this->attributes['contact_hobby'] ?? $this->attributes['hobby'] ?? null;
    }

    // Accessor kompatibilitas notifikasi lama
    public function getNamaUserAttribute()
    {
        return $this->attributes['client_name'] ?? $this->attributes['contact_name'] ?? null;
    }

    public function getNamaPerusahaanAttribute()
    {
        return $this->customer?->customer_name ?? $this->attributes['customer_name'] ?? null;
    }

    public function getTanggalLahirAttribute()
    {
        return $this->birthdate;
    }

    public function interactions()
    {
        return $this->hasMany(ClientInteraction::class)->orderBy('interaction_date', 'desc');
    }

    public function picHistories()
    {
        return $this->hasMany(ClientPicHistory::class)->orderBy('start_date', 'desc')->orderBy('id', 'desc');
    }

    public function activePicHistory()
    {
        return $this->hasOne(ClientPicHistory::class)->whereNull('end_date')->latestOfMany('start_date');
    }

    public function getTotalKontribusiAttribute()
    {
        if (!$this->relationLoaded('interactions')) {
            $this->load('interactions');
        }

        $pemasukan = $this->interactions
                          ->where('transaction_type', 'IN')
                          ->sum(fn ($item) => $item->sales_amount > 0 ? $item->sales_amount : $item->amount);

        $pengeluaran = $this->interactions
                            ->where('transaction_type', 'OUT')
                            ->sum('amount');

        return $pemasukan - $pengeluaran;
    }
}
