<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
   protected $fillable = [
    'company_id',
        'product_code',
    'product_name',
    'category',
    'white_rate',
    'color_rate',
    'unit',
    'profile_length',
    'bundle_qty',
    'weight_per_meter',
        'sinewy_white_rate',
        'sinewy_color_rate',
        'assre_white_rate',
        'assre_color_rate',
    'mrp',
    'discount_pct',
    'is_active',
];

    protected $casts = [
        'is_active'      => 'boolean',
        'white_rate'     => 'float',
        'color_rate'     => 'float',
        'profile_length' => 'float',
        'mrp'            => 'float',
        'discount_pct'   => 'float',
        'weight_per_meter' => 'float',
    ];
}