<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Category;
use App\Models\ContactEnquiry;
use App\Models\Customer;
use App\Models\Eyewear;
use App\Models\EyeCheckupBill;
use App\Models\FrameBill;
use App\Models\Order;
use App\Models\Review;
use App\Models\StockItem;

class DashboardController extends Controller
{
    public function index()
    {
        $eyewears = Eyewear::query()->count();
        $categories = Category::query()->count();
        $customers = Customer::query()->count();
        $orders = Order::query()->count();
        $pending_reviews = Review::query()->where('is_approved', false)->count();
        $stock_items = StockItem::query()->count();
        $frame_bills = FrameBill::query()->count();
        $eye_checkup_bills = EyeCheckupBill::query()->count();
        $enquiries = ContactEnquiry::query()->count();
        $appointments = Appointment::query()->where('status', 'pending')->count();

        return $this->data([
            'eyewears' => $eyewears,
            'categories' => $categories,
            'customers' => $customers,
            'orders' => $orders,
            'pending_reviews' => $pending_reviews,
            'stock_items' => $stock_items,
            'frame_bills' => $frame_bills,
            'eye_checkup_bills' => $eye_checkup_bills,
            'enquiries' => $enquiries,
            'appointments' => $appointments,
        ]);
    }
}
