<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $table = 'work_orders';
    protected $fillable = [
        'order_number', 'branch_id', 'customer_id', 'frame_bill_id', 'customer_name', 'customer_contact', 'description',
        'status', 'expected_date', 'ready_at', 'delivered_at', 'notified_at', 'notes', 'created_by',
    ];
    protected $casts = ['expected_date' => 'date:Y-m-d', 'ready_at' => 'datetime', 'delivered_at' => 'datetime', 'notified_at' => 'datetime'];

    public const STATUSES = ['ordered', 'in_fitting', 'ready', 'delivered', 'cancelled'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
