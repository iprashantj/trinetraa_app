<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offers';
    protected $fillable = [
        'title', 'description', 'image', 'badge', 'valid_from', 'valid_to', 'sort_order', 'is_active',
    ];
    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
