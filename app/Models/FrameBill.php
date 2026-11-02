<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrameBill extends Model
{
    protected $table = 'frame_bills';
    protected $fillable = [
        'bill_number', 'customer_id', 'customer_name', 'customer_contact', 'bill_date',
        'discount_amount', 'gst_rate', 'subtotal', 'tax', 'total', 'notes', 'branch_id', 'payment_method', 'payment_status', 'paid_amount', 'share_token', 'payment_link_id', 'payment_link_url', 'created_by',
        'invoice_no', 'customer_gstin', 'cgst', 'sgst', 'coupon_code', 'coupon_discount', 'loyalty_points_used', 'loyalty_discount', 'review_requested_at',
    ];
    protected $casts = [
        'bill_date' => 'date',
        'discount_amount' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'loyalty_discount' => 'decimal:2',
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
