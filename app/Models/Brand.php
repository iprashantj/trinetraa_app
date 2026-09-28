<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';
    protected $fillable = [
        'name', 'slug', 'logo', 'story', 'description', 'website', 'sort_order', 'is_active',
    ];
    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];
}
