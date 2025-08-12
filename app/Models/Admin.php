<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    protected $connection = 'mongodb';
    protected $collection = 'admins';

    protected $fillable = ['username', 'password', 'login_at', 'created_at'];

    public function getJWTIdentifier()
    {
        return $this->_id;
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
