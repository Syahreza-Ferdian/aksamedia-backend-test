<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Traits\WithUUID;

class AdminModel extends Authenticatable implements JWTSubject
{
    use HasFactory, WithUUID;

    protected $table = 'admin';

    protected $fillable = [
        'name',
        'username',
        'password_hash',
        'phone',
        'email'
    ];

    protected $hidden = [
        'password_hash',
        'created_at',
        'updated_at'
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
