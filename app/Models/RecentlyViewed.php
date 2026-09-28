<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentlyViewed extends Model
{
    protected $table = 'recently_viewed';
    protected $fillable = ['customer_id', 'eyewear_id', 'viewed_at'];
    public $timestamps = false;
    protected $casts = ['viewed_at' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(CustomerUser::class, 'customer_id');
    }

    public function eyewear()
    {
        return $this->belongsTo(Eyewear::class, 'eyewear_id');
    }
}
