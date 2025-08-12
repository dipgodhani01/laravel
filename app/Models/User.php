<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'image',
        'email_verified_at',
    ];

    protected $hidden = [
        'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->_id;
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}