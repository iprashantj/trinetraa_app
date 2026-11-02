<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password_hash', 'role', 'is_active', 'branch_id', 'permissions', 'totp_secret', 'totp_enabled', 'failed_logins', 'locked_until'];
    protected $hidden = ['password_hash', 'totp_secret'];
    protected $casts = ['is_active' => 'boolean', 'permissions' => 'array', 'totp_enabled' => 'boolean', 'locked_until' => 'datetime'];
}
