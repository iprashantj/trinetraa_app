<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrameBill extends Model
{
    protected $table = 'frame_bills';
    protected $fillable = [
        'bill_number', 'customer_id', 'customer_name', 'customer_contact', 'bill_date',
        'discount_amount', 'gst_rate', 'subtotal', 'tax', 'total', 'notes',
    ];
    protected $casts = [
        'bill_date' => 'date',
        'discount_amount' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(FrameBillItem::class, 'frame_bill_id');
    }
}
