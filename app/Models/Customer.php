<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $fillable = ['name', 'email', 'phone', 'address', 'dob', 'marketing_consent', 'consent_at', 'whatsapp_opt_in', 'branch_id'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function frameBills()
    {
        return $this->hasMany(FrameBill::class, 'customer_id');
    }

    public function eyeCheckupBills()
    {
        return $this->hasMany(EyeCheckupBill::class, 'customer_id');
    }
}
