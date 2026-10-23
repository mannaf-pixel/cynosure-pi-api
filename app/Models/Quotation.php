<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_number', 'company_id', 'customer_id', 'created_by',
        'brand', 'salesperson_name', 'color_name',
        'white_discount_pct', 'color_discount_pct', 'hardware_discount_pct',
        'cd_discount_pct', 'cd_discount_amount',
        'transport_charge', 'insurance_pct', 'insurance_charge',
        'subtotal', 'gst_amount', 'grand_total',
        'status', 'valid_until', 'remarks', 'pi_id',
    ];

    protected $casts = [
        'subtotal'             => 'float',
        'gst_amount'           => 'float',
        'grand_total'          => 'float',
        'white_discount_pct'   => 'float',
        'color_discount_pct'   => 'float',
        'hardware_discount_pct'=> 'float',
        'cd_discount_pct'      => 'float',
        'cd_discount_amount'   => 'float',
        'transport_charge'     => 'float',
        'insurance_pct'        => 'float',
        'insurance_charge'     => 'float',
        'valid_until'          => 'date',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function items()    { return $this->hasMany(QuotationItem::class)->orderBy('sort_order'); }
    public function pi()       { return $this->belongsTo(PiMaster::class, 'pi_id'); }
}
