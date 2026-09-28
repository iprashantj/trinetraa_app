<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EyeCheckupBill extends Model
{
    protected $table = 'eye_checkup_bills';
    protected $fillable = [
        'bill_number', 'customer_id', 'customer_name', 'customer_contact', 'bill_date',
        'left_eye', 'right_eye', 'addition', 'frame_amount', 'glass_amount', 'advance_amount',
        'other_amount', 'with_gst', 'gst_rate', 'subtotal', 'tax', 'total', 'balance_due', 'notes',
    ];
    protected $casts = [
        'bill_date' => 'date',
        'with_gst' => 'boolean',
        'frame_amount' => 'decimal:2',
        'glass_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'other_amount' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];
}
