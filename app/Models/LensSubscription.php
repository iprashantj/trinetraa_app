<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LensSubscription extends Model
{
    protected $table = 'lens_subscriptions';
    protected $fillable = [
        'customer_id', 'lens_name', 'brand', 'power', 'interval_days', 'next_due_date',
        'status', 'address_line', 'notes',
    ];
    protected $casts = ['next_due_date' => 'date', 'interval_days' => 'integer'];

    public function customer()
    {
        return $this->belongsTo(CustomerUser::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(SubscriptionOrder::class, 'subscription_id');
    }
}
