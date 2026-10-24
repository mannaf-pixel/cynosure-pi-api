<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = [
        'company_id', 'item_type', 'product_id', 'hardware_product_id',
        'item_code', 'item_name', 'quantity', 'unit', 'low_stock_alert',
    ];

    protected $casts = [
        'quantity'         => 'float',
        'low_stock_alert'  => 'float',
    ];

    public function product()         { return $this->belongsTo(Product::class); }
    public function hardwareProduct() { return $this->belongsTo(HardwareProduct::class); }
    public function transactions()    { return $this->hasMany(StockTransaction::class)->orderBy('created_at', 'desc'); }

    public function getIsLowStockAttribute()
    {
        return $this->low_stock_alert > 0 && $this->quantity <= $this->low_stock_alert;
    }
}
