<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupons';
    protected $fillable = ['code', 'description', 'type', 'value', 'min_amount', 'max_discount', 'brand', 'valid_from', 'valid_to', 'usage_limit', 'used_count', 'is_active'];
    protected $casts = ['value' => 'float', 'min_amount' => 'float', 'max_discount' => 'float', 'valid_from' => 'date', 'valid_to' => 'date',
        'usage_limit' => 'integer', 'used_count' => 'integer', 'is_active' => 'boolean'];
}
