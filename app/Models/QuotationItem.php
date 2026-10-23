<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'item_type',
        'product_id', 'hardware_product_id',
        'product_code_snap', 'product_name_snap',
        'hardware_name_snap', 'hardware_unit_snap',
        'profile_type_snap', 'color_name',
        'unit_rate_snap', 'mrp_rate_snap', 'item_discount_pct',
        'profile_length_snap', 'weight_per_meter_snap',
        'bundle_qty_ordered', 'total_length', 'total_pieces',
        'total_weight', 'quantity', 'line_total', 'sort_order',
    ];

    protected $casts = [
        'unit_rate_snap'        => 'float',
        'mrp_rate_snap'         => 'float',
        'profile_length_snap'   => 'float',
        'weight_per_meter_snap' => 'float',
        'total_length'          => 'float',
        'total_weight'          => 'float',
        'line_total'            => 'float',
        'item_discount_pct'     => 'float',
    ];
}
