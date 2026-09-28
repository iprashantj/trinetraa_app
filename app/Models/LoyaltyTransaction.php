<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    protected $table = 'loyalty_transactions';
    protected $fillable = ['account_id', 'type', 'points', 'description', 'reference_id', 'created_at'];
    public $timestamps = false;
    protected $casts = ['points' => 'integer', 'created_at' => 'datetime'];

    public function account()
    {
        return $this->belongsTo(LoyaltyAccount::class, 'account_id');
    }
}
