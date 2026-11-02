<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Support\Messenger;
use Illuminate\Http\Request;

/** Customer-facing status lookup: order number AND the phone on the order must both match. */
class WorkOrderStatusController extends Controller
{
    public function show(Request $request)
    {
        $number = trim((string) $request->query('order_number', ''));
        $phone = Messenger::phone((string) $request->query('phone', ''));
        $row = ($number !== '' && $phone) ? WorkOrder::where('order_number', $number)->first() : null;
        // One generic answer for "no such order" and "wrong phone", so order numbers cannot be probed.
        if (! $row || Messenger::phone($row->customer_contact) !== $phone) {
            return $this->message('No order found for those details.', 404);
        }

        return $this->data(['status' => $row->status, 'expected_date' => $row->expected_date?->toDateString()]);
    }
}
