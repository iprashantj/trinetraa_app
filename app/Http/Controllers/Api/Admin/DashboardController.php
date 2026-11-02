<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Category;
use App\Models\ContactEnquiry;
use App\Models\Customer;
use App\Models\Eyewear;
use App\Models\EyeCheckupBill;
use App\Models\FrameBill;
use App\Models\Order;
use App\Models\Review;
use App\Models\StockItem;
use App\Models\StockLevel;
use App\Models\StockTransfer;
use App\Support\Access;
use App\Support\StockService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = Access::branchScope($request);

        $eyewears = Eyewear::query()->count();
        $categories = Category::query()->count();
        $customers = Customer::query()->count();
        $orders = Order::query()->count();
        $pending_reviews = Review::query()->where('is_approved', false)->count();
        $stock_items = StockItem::query()->count();
        $frame_bills = FrameBill::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->count();
        $eye_checkup_bills = EyeCheckupBill::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->count();
        $enquiries = ContactEnquiry::query()->count();
        $appointments = Appointment::query()->where('status', 'pending')->count();

        // Last 12 months of billed revenue (frame bills + eye checkup bills), grouped in PHP so it stays DB-agnostic.
        $from = now()->startOfMonth()->subMonths(11);
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $from->copy()->addMonths($i);
            $months[$m->format('Y-m')] = ['label' => $m->format('M/y'), 'frame' => 0.0, 'eye' => 0.0];
        }
        foreach (FrameBill::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->where('bill_date', '>=', $from->toDateString())->get(['bill_date', 'total']) as $b) {
            $k = $b->bill_date?->format('Y-m');
            if (isset($months[$k])) $months[$k]['frame'] += (float) $b->total;
        }
        foreach (EyeCheckupBill::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->where('bill_date', '>=', $from->toDateString())->get(['bill_date', 'total']) as $b) {
            $k = $b->bill_date?->format('Y-m');
            if (isset($months[$k])) $months[$k]['eye'] += (float) $b->total;
        }
        $monthly = array_values($months);
        $revenue_total = array_sum(array_map(fn ($m) => $m['frame'] + $m['eye'], $monthly));

        $recent_bills = FrameBill::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')->limit(5)->get(['id', 'bill_number', 'customer_name', 'total', 'bill_date'])
            ->map(fn ($b) => [
                'bill_number' => $b->bill_number,
                'customer_name' => $b->customer_name,
                'total' => (float) $b->total,
                'bill_date' => $b->bill_date?->format('d M Y'),
            ]);

        // Owner dashboard: sales today / by branch, stock, alerts, transfers on the way.
        $today = now()->toDateString();
        $salesToday = (float) FrameBill::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->where('bill_date', $today)->sum('total')
            + (float) EyeCheckupBill::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->where('bill_date', $today)->sum('total');
        $salesYesterday = (float) FrameBill::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->where('bill_date', now()->subDay()->toDateString())->sum('total')
            + (float) EyeCheckupBill::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->where('bill_date', now()->subDay()->toDateString())->sum('total');

        $admin = Access::admin($request);
        $pinned = ! Access::isOwner($admin) && ! empty($admin['branch_id']) ? (int) $admin['branch_id'] : null;
        $byBranch = Branch::query()->when($pinned, fn ($q) => $q->where('id', $pinned))->orderBy('name')->get(['id', 'name'])->map(fn ($b) => [
            'branch_id' => $b->id, 'name' => $b->name,
            'sales_today' => (float) FrameBill::where('branch_id', $b->id)->where('bill_date', $today)->sum('total')
                + (float) EyeCheckupBill::where('branch_id', $b->id)->where('bill_date', $today)->sum('total'),
            'stock' => (int) StockLevel::where('branch_id', $b->id)->sum('quantity'),
        ])->values();

        $alerts = StockService::lowStock($branchId);
        $transfersInTransit = StockTransfer::where('status', 'in_transit')
            ->when($branchId, fn ($q) => $q->where(fn ($w) => $w->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId)))->count();
        $recentActivity = \App\Models\AdminActivityLog::query()->orderByDesc('id')->limit(6)->get(['action', 'user_email', 'created_at'])
            ->map(fn ($a) => ['action' => $a->action, 'user' => $a->user_email, 'at' => $a->created_at?->format('H:i')]);

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
            'monthly' => $monthly,
            'revenue_total' => $revenue_total,
            'recent_bills' => $recent_bills,
            'sales_today' => $salesToday,
            'sales_yesterday' => $salesYesterday,
            'items_in_stock' => (int) StockLevel::when($branchId, fn ($q) => $q->where('branch_id', $branchId))->sum('quantity'),
            'branches_count' => $byBranch->count(),
            'low_stock_count' => count($alerts),
            'low_stock' => array_slice($alerts, 0, 6),
            'transfers_in_transit' => $transfersInTransit,
            'by_branch' => $byBranch,
            'recent_activity' => $recentActivity,
        ]);
    }
}
