<?php

namespace App\Models;

use App\Models\Concerns\SoftDeletesFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory, SoftDeletesFlag;

    protected $fillable = [
        'user_id',
        'area',
        'ps',

        // Contact person
        'client_name',
        'email',
        'contact_phone',
        'contact_address',
        'contact_birth_date',
        'contact_position',
        'contact_hobby',

        // Pharmacist / commission
        'pharmacist_name',
        'pharmacist_license_no',
        'pharmacist_phone',
        'commission_rate',

        // Company
        'customer_name',
        'sales_customer_name',
        'company_founded_date',
        'company_address',

        // Bank
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'opening_balance',
        'is_deleted',
    ];

    protected $casts = [
        'company_founded_date' => 'date',
        'contact_birth_date' => 'date',
        'is_deleted' => 'boolean',
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
        return $this->hasMany(ClientInteraction::class)->orderBy('interaction_date', 'desc');
    }

    public function getTotalKontribusiAttribute()
    {
        if (!$this->relationLoaded('interactions')) {
            $this->load('interactions');
        }

        $pemasukan = $this->interactions
                          ->where('transaction_type', 'IN')
                          ->sum('amount');

        $pengeluaran = $this->interactions
                            ->where('transaction_type', 'OUT')
                            ->sum('amount');

        return $pemasukan - $pengeluaran;
    }
}