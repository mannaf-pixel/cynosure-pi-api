<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'pi_id',
        'approver_id',
        'action',
        'approver_role',
        'comments',
        'actioned_at',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function pi()
    {
        return $this->belongsTo(PiMaster::class, 'pi_id');
    }
}