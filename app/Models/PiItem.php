<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiItem extends Model
{
    protected $table = 'pi_items';

    protected $fillable = [
        'pi_id',
        'item_type',
        'product_id',
        'hardware_product_id',
        'hardware_name_snap',
        'hardware_unit_snap',
        'profile_type_snap',
        'color_name',
        'product_code_snap',
        'product_name_snap',
        'unit_rate_snap',
        'profile_length_snap',
        'bundle_qty_ordered',
        'total_length',
        'total_pieces',
        'total_weight',
        'quantity',
        'line_total',
        'sort_order',
        'weight_per_meter_snap',
    ];

    protected $casts = [
        'unit_rate_snap'      => 'float',
        'profile_length_snap' => 'float',
        'total_length'        => 'float',
        'line_total'          => 'float',
        'weight_per_meter_snap' => 'float',
        'total_weight'          => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}