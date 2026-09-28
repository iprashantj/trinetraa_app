<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionOrder extends Model
{
    protected $table = 'subscription_orders';
    protected $fillable = ['subscription_id', 'ordered_at', 'status', 'notes'];
    public $timestamps = false;
    protected $casts = ['ordered_at' => 'datetime'];

    public function subscription()
    {
        return $this->belongsTo(LensSubscription::class, 'subscription_id');
    }
}
