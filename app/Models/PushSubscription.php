<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $table = 'push_subscriptions';
    protected $fillable = ['customer_id', 'endpoint', 'p256dh', 'auth', 'created_at'];
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(CustomerUser::class, 'customer_id');
    }
}
