<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrameBillItem extends Model
{
    protected $table = 'frame_bill_items';
    protected $fillable = [
        'frame_bill_id', 'brand_name', 'model_number', 'price', 'discount', 'quantity', 'subtotal',
    ];
    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    public function frameBill()
    {
        return $this->belongsTo(FrameBill::class, 'frame_bill_id');
    }
}
