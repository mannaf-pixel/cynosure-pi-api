<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'company_id',
        'customer_name',
        'company_name',
        'gstin',
        'contact_person',
        'mobile',
        'email',
        'billing_address',
        'shipping_address',
        'state',
        'city',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function piHistory()
    {
        return $this->hasMany(PiMaster::class, 'customer_id');
    }
}