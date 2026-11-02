<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillReturn;
use App\Models\BillReturnItem;
use App\Models\Branch;
use App\Models\Category;
use App\Models\FrameBill;
use App\Models\FrameBillItem;
use App\Models\StockLevel;
use App\Support\Access;
use Illuminate\Http\Request;

/** Stock and sales reports, by branch and for all branches. */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $admin = Access::admin($request);
        $from = $request->query('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->query('to') ?: now()->toDateString();
        $pinned = ! Access::isOwner($admin) && ! empty($admin['branch_id']) ? (int) $admin['branch_id'] : null;
        $branches = Branch::query()->when($pinned, fn ($q) => $q->where('id', $pinned))->orderBy('name')->get(['id', 'name', 'code']);

        $stockQty = StockLevel::selectRaw('branch_id, SUM(quantity) q')->groupBy('branch_id')->pluck('q', 'branch_id');
        $skus = StockLevel::where('quantity', '>', 0)->selectRaw('branch_id, COUNT(*) c')->groupBy('branch_id')->pluck('c', 'branch_id');

        $bills = FrameBill::whereBetween('bill_date', [$from, $to])->get(['id', 'branch_id', 'total', 'payment_status']);
        $billItemQty = FrameBillItem::whereIn('frame_bill_id', $bills->pluck('id'))->selectRaw('frame_bill_id, SUM(quantity) q')->groupBy('frame_bill_id')->pluck('q', 'frame_bill_id');
        $returns = BillReturn::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->get();
        $returnQty = BillReturnItem::whereIn('bill_return_id', $returns->pluck('id'))->selectRaw('bill_return_id, SUM(quantity) q')->groupBy('bill_return_id')->pluck('q', 'bill_return_id');

        $rows = $branches->map(function ($b) use ($stockQty, $skus, $bills, $billItemQty, $returns, $returnQty) {
            $bb = $bills->where('branch_id', $b->id);
            $rr = $returns->where('branch_id', $b->id);

            return [
                'branch_id' => $b->id, 'branch' => $b->name,
                'stock_quantity' => (int) ($stockQty[$b->id] ?? 0), 'stock_lines' => (int) ($skus[$b->id] ?? 0),
                'invoices' => $bb->count(), 'items_sold' => (int) $bb->sum(fn ($x) => $billItemQty[$x->id] ?? 0),
                'sales' => round((float) $bb->sum('total'), 2),
                'returns' => $rr->count(), 'items_returned' => (int) $rr->sum(fn ($x) => $returnQty[$x->id] ?? 0),
                'refunded' => round((float) $rr->sum('refund_amount'), 2),
            ];
        })->values();

        $totals = [
            'branch' => 'All branches',
            'stock_quantity' => $rows->sum('stock_quantity'), 'stock_lines' => $rows->sum('stock_lines'),
            'invoices' => $rows->sum('invoices'), 'items_sold' => $rows->sum('items_sold'), 'sales' => round($rows->sum('sales'), 2),
            'returns' => $rows->sum('returns'), 'items_returned' => $rows->sum('items_returned'), 'refunded' => round($rows->sum('refunded'), 2),
        ];

        $cats = Category::pluck('name', 'id');
        $byCategory = \App\Models\StockItem::query()->get(['id', 'category_id', 'brand'])->groupBy('category_id')->map(function ($items, $cid) use ($cats) {
            return [
                'category' => $cats[$cid] ?? 'Uncategorised',
                'quantity' => (int) StockLevel::whereIn('stock_item_id', $items->pluck('id'))->sum('quantity'),
            ];
        })->values();

        if ($request->query('format') === 'csv') {
            $lines = [['Branch', 'Stock qty', 'Stock lines', 'Invoices', 'Items sold', 'Sales', 'Returns', 'Items returned', 'Refunded']];
            foreach ($rows->push($totals) as $r) {
                $lines[] = [$r['branch'], $r['stock_quantity'], $r['stock_lines'], $r['invoices'], $r['items_sold'], $r['sales'], $r['returns'], $r['items_returned'], $r['refunded']];
            }
            $csv = implode("\r\n", array_map(fn ($l) => implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $l)), $lines));

            return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=report-$from-to-$to.csv"]);
        }

        return $this->data(['from' => $from, 'to' => $to, 'branches' => $rows, 'totals' => $totals, 'by_category' => $byCategory]);
    }
}
