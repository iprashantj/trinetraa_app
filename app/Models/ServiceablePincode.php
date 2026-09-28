<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceablePincode extends Model
{
    protected $table = 'serviceable_pincodes';
    protected $fillable = ['pincode', 'area', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
