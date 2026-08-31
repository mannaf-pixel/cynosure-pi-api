<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'profile_type',
        'old_rate',
        'new_rate',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'old_rate'   => 'float',
        'new_rate'   => 'float',
        'changed_at' => 'datetime',
    ];
}