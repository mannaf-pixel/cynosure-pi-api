<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'pricing_mode',
        'gstin',
        'address',
        'phone',
        'email',
        'bank_name',
        'account_name',
        'account_number',
        'ifsc_code',
        'branch',
        'logo',
        'primary_color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function piMasters()
    {
        return $this->hasMany(PiMaster::class);
    }

    public function isPricingPerKg(): bool
    {
        return $this->pricing_mode === 'per_kg';
    }

    public function isPricingPerMeter(): bool
    {
        return $this->pricing_mode === 'per_meter';
    }
}