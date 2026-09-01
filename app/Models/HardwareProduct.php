<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HardwareProduct extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'unit',
        'rate',
        'is_active',
    ];

    protected $casts = [
        'rate'      => 'float',
        'is_active' => 'boolean',
    ];
}
