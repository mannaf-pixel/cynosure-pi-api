<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'pi_id',
        'customer_id',
        'payment_date',
        'amount',
        'payment_mode',
        'received_in',
        'transaction_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'float',
    ];

    public function pi()
    {
        return $this->belongsTo(PiMaster::class, 'pi_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
