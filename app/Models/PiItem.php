<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiItem extends Model
{
    protected $table = 'pi_items';

    protected $fillable = [
        'pi_id',
        'product_id',
        'product_code_snap',
        'product_name_snap',
        'unit_rate_snap',
        'profile_length_snap',
        'bundle_qty_ordered',
        'total_length',
        'total_pieces',
        'line_total',
        'sort_order',
        'weight_per_meter_snap',
        'total_weight',
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