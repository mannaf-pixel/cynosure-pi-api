<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiMaster extends Model
{
    protected $table = 'pi_master';

    protected $fillable = [
        'company_id',
        'pi_number',
        'customer_id',
        'profile_type',
        'status',
        'transport_charge',
        'insurance_charge',
        'subtotal',
        'gst_amount',
        'grand_total',
        'remarks',
        'created_by',
        'salesperson_id',
        'salesperson_name',
        'submitted_at',
        'discount_pct',
        'discount_amount',
        'actual_amount',
        'payment_mode',
        'received_in',
        'payment_note',
        'brand',
        'color_name',
        'transport_company',
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'lr_number',
        'freight_amount',
        'dispatch_note',
        'dispatch_photo',
        'transport_copy',
        'dispatched_at',
        'expected_dispatch_date',
        'delivery_scheduled_at',
    ];

    protected $casts = [
        'transport_charge' => 'float',
        'insurance_charge' => 'float',
        'subtotal'         => 'float',
        'discount_pct'    => 'float',
        'discount_amount' => 'float',
        'gst_amount'       => 'float',
        'grand_total'      => 'float',
        'submitted_at'     => 'datetime',
        'brand',
        'transport_company',
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'lr_number',
        'freight_amount',
        'dispatch_note',
        'dispatch_photo',
        'transport_copy',
        'dispatched_at',
        'expected_dispatch_date',
        'delivery_scheduled_at',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(PiItem::class, 'pi_id')->orderBy('sort_order');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'pi_id');
    }

    public function salesperson()
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}