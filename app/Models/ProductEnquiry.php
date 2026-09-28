<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEnquiry extends Model
{
    protected $table = 'product_enquiries';
    protected $fillable = ['name', 'email', 'phone', 'eyewear_id', 'eyewear_name', 'message'];

    public function eyewear()
    {
        return $this->belongsTo(Eyewear::class, 'eyewear_id');
    }
}
