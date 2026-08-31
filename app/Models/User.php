<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'company_id',
        'is_super_admin',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active'      => 'boolean',
        'is_super_admin' => 'boolean',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role'           => $this->role,
            'name'           => $this->name,
            'company_id'     => $this->company_id,
            'is_super_admin' => $this->is_super_admin,
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isSuperAdmin(): bool { return $this->is_super_admin === true; }
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isPiCreator(): bool  { return $this->role === 'pi_creator'; }
    public function isMd(): bool         { return $this->role === 'md'; }
    public function isCeo(): bool        { return $this->role === 'ceo'; }
}