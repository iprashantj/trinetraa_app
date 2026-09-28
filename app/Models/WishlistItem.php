<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    protected $table = 'wishlist_items';
    protected $fillable = ['customer_id', 'eyewear_id', 'added_at'];
    public $timestamps = false;
    protected $casts = ['added_at' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(CustomerUser::class, 'customer_id');
    }

    public function eyewear()
    {
        return $this->belongsTo(Eyewear::class, 'eyewear_id');
    }
}
