<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerUser extends Model
{
    protected $table = 'customer_users';
    protected $fillable = ['name', 'email', 'phone', 'password_hash', 'face_shape'];
    protected $hidden = ['password_hash'];

    public function recentlyViewed()
    {
        return $this->hasMany(RecentlyViewed::class, 'customer_id');
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class, 'customer_id');
    }

    public function loyaltyAccount()
    {
        return $this->hasOne(LoyaltyAccount::class, 'customer_id');
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class, 'customer_id');
    }

    public function lensSubscriptions()
    {
        return $this->hasMany(LensSubscription::class, 'customer_id');
    }
}
