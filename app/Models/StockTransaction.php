<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = [
        'stock_id', 'company_id', 'type', 'quantity',
        'quantity_before', 'quantity_after',
        'reference_type', 'reference_id', 'note', 'created_by',
    ];

    protected $casts = [
        'quantity'        => 'float',
        'quantity_before' => 'float',
        'quantity_after'  => 'float',
    ];

    public function stock()   { return $this->belongsTo(Stock::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
