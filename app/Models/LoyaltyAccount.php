<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyAccount extends Model
{
    protected $table = 'loyalty_accounts';
    protected $fillable = ['customer_id', 'points', 'tier', 'total_earned'];
    protected $casts = ['points' => 'integer', 'total_earned' => 'integer'];

    public function customer()
    {
        return $this->belongsTo(CustomerUser::class, 'customer_id');
    }

    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'account_id');
    }
}
