<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillReturn;
use App\Models\EyeCheckupBill;
use App\Models\FrameBill;
use App\Models\FrameBillItem;
use App\Models\StockItem;
use App\Models\StockLevel;
use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** GST sales register and profit / margin analysis. */
class FinanceReportController extends Controller
{
    private function range(Request $r): array
    {
        $from = $r->query('from') ?: now()->startOfMonth()->toDateString();
        $to = $r->query('to') ?: now()->toDateString();

        return [$from, $to];
    }

    private function csv(string $name, array $head, array $rows)
    {
        $out = fopen('php://temp', 'w+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $head);
        foreach ($rows as $row) {
            fputcsv($out, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $row));
        }
        rewind($out);

        return response(stream_get_contents($out), 200, ['Content-Type' => 'text/csv; charset=utf-8', 'Content-Disposition' => 'attachment; filename="' . $name . '"']);
    }

    /** Month/period GST register for the accountant: taxable value and CGST/SGST per invoice, summarised by rate. */
    public function gst(Request $request)
    {
        [$from, $to] = $this->range($request);
        $branchId = Access::branchScope($request);

        $frame = FrameBill::whereBetween('bill_date', [$from, $to])->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->orderBy('bill_date')->orderBy('id')->get();
        $eye = EyeCheckupBill::whereBetween('bill_date', [$from, $to])->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->orderBy('bill_date')->orderBy('id')->get();

        $rows = [];
        foreach ($frame as $b) {
            [$c, $s] = [(float) ($b->cgst ?: 0), (float) ($b->sgst ?: 0)];
            if (! $c && ! $s && (float) $b->tax > 0) { // bills made before the split existed
                $c = round($b->tax / 2, 2);
                $s = round($b->tax - $c, 2);
            }
            $rows[] = ['invoice_no' => $b->invoice_no ?: $b->bill_number, 'date' => $b->bill_date->toDateString(), 'type' => 'Frame', 'customer' => $b->customer_name,
                'gstin' => $b->customer_gstin, 'rate' => (float) $b->gst_rate, 'taxable' => (float) $b->subtotal, 'cgst' => $c, 'sgst' => $s, 'total' => (float) $b->total];
        }
        foreach ($eye as $b) {
            $tax = $b->with_gst ? (float) $b->tax : 0.0;
            $c = round($tax / 2, 2);
            $rows[] = ['invoice_no' => $b->invoice_no ?: $b->bill_number, 'date' => $b->bill_date->toDateString(), 'type' => 'Eye checkup', 'customer' => $b->customer_name,
                'gstin' => null, 'rate' => $b->with_gst ? (float) $b->gst_rate : 0.0, 'taxable' => (float) $b->subtotal, 'cgst' => $c, 'sgst' => round($tax - $c, 2), 'total' => (float) $b->total];
        }
        usort($rows, fn ($a, $b) => [$a['date'], $a['invoice_no']] <=> [$b['date'], $b['invoice_no']]);

        $byRate = [];
        foreach ($rows as $r) {
            $k = number_format($r['rate'], 2);
            $byRate[$k] ??= ['rate' => $r['rate'], 'invoices' => 0, 'taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'total' => 0];
            $byRate[$k]['invoices']++;
            foreach (['taxable', 'cgst', 'sgst', 'total'] as $f) {
                $byRate[$k][$f] = round($byRate[$k][$f] + $r[$f], 2);
            }
        }
        ksort($byRate);
        $returns = (float) BillReturn::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->sum('refund_amount');
        $totals = ['invoices' => count($rows)] + array_map(fn ($f) => round(array_sum(array_column($rows, $f)), 2), ['taxable' => 'taxable', 'cgst' => 'cgst', 'sgst' => 'sgst', 'total' => 'total']);

        if ($request->query('format') === 'csv') {
            return $this->csv("gst-register-$from-to-$to.csv", ['Invoice', 'Date', 'Type', 'Customer', 'Customer GSTIN', 'GST %', 'Taxable', 'CGST', 'SGST', 'Total'],
                array_map(fn ($r) => array_values($r), $rows));
        }

        return $this->data(['from' => $from, 'to' => $to, 'rows' => $rows, 'by_rate' => array_values($byRate), 'totals' => $totals, 'returns_refunded' => round($returns, 2)]);
    }

    /** Margin by brand, slow-moving stock and sales per staff member. Cost comes from the cost price saved on each sale line. */
    public function profit(Request $request)
    {
        [$from, $to] = $this->range($request);
        $branchId = Access::branchScope($request);
        $slowDays = max(7, min(720, (int) ($request->query('slow_days') ?: 90)));

        $bills = FrameBill::whereBetween('bill_date', [$from, $to])->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->get(['id', 'subtotal', 'created_by']);
        $items = FrameBillItem::whereIn('frame_bill_id', $bills->pluck('id'))->get();
        $returned = DB::table('bill_return_items')->whereIn('frame_bill_item_id', $items->pluck('id'))->selectRaw('frame_bill_item_id, SUM(quantity) q')->groupBy('frame_bill_item_id')->pluck('q', 'frame_bill_item_id');
        $billMap = $bills->keyBy('id');
        $gross = $items->groupBy('frame_bill_id')->map(fn ($g) => (float) $g->sum('subtotal'));

        $brands = [];
        $unknown = 0;
        $lines = 0;
        foreach ($items as $it) {
            $bill = $billMap[$it->frame_bill_id];
            $factor = ($gross[$it->frame_bill_id] ?? 0) > 0 ? (float) $bill->subtotal / $gross[$it->frame_bill_id] : 1; // spread bill-level discounts over lines
            $qty = max(0, (int) $it->quantity - (int) ($returned[$it->id] ?? 0));
            $unitNet = (int) $it->quantity > 0 ? (float) $it->subtotal * $factor / (int) $it->quantity : 0;
            $b = trim((string) $it->brand_name) ?: 'Unbranded';
            $brands[$b] ??= ['brand' => $b, 'units' => 0, 'revenue' => 0.0, 'cost' => 0.0, 'costed_revenue' => 0.0];
            $brands[$b]['units'] += $qty;
            $brands[$b]['revenue'] += $unitNet * $qty;
            $lines++;
            if ($it->cost_price !== null) {
                $brands[$b]['cost'] += (float) $it->cost_price * $qty;
                $brands[$b]['costed_revenue'] += $unitNet * $qty;
            } else {
                $unknown++;
            }
        }
        $brandRows = collect($brands)->map(function ($r) {
            $margin = $r['costed_revenue'] - $r['cost'];

            return ['brand' => $r['brand'], 'units' => $r['units'], 'revenue' => round($r['revenue'], 2), 'cost' => round($r['cost'], 2), 'margin' => round($margin, 2),
                'margin_pct' => $r['costed_revenue'] > 0 ? round($margin / $r['costed_revenue'] * 100, 1) : null];
        })->sortByDesc('revenue')->values();

        $lastSale = DB::table('stock_movements')->where('type', 'sale')->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('stock_item_id, MAX(created_at) last_at')->groupBy('stock_item_id')->pluck('last_at', 'stock_item_id');
        $qty = StockLevel::when($branchId, fn ($q) => $q->where('branch_id', $branchId))->where('quantity', '>', 0)
            ->selectRaw('stock_item_id, SUM(quantity) q')->groupBy('stock_item_id')->pluck('q', 'stock_item_id');
        $cut = now()->subDays($slowDays);
        $slow = StockItem::whereIn('id', $qty->keys())->get()->filter(fn ($s) => ! isset($lastSale[$s->id]) || \Illuminate\Support\Carbon::parse($lastSale[$s->id])->lt($cut))
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'brand' => $s->brand, 'quantity' => (int) $qty[$s->id],
                'last_sold' => isset($lastSale[$s->id]) ? substr($lastSale[$s->id], 0, 10) : null,
                'stock_value' => round((float) $s->cost_price * (int) $qty[$s->id], 2)])
            ->sortByDesc('stock_value')->values();

        $users = User::whereIn('id', $bills->pluck('created_by')->filter())->pluck('name', 'id');
        $staff = $bills->groupBy(fn ($b) => $b->created_by ?: 0)->map(fn ($g, $uid) => [
            'staff' => $uid ? ($users[$uid] ?? 'User #' . $uid) : 'Unassigned', 'invoices' => $g->count(), 'sales' => round((float) $g->sum('subtotal'), 2),
        ])->sortByDesc('sales')->values();

        $tot = ['revenue' => round($brandRows->sum('revenue'), 2), 'cost' => round($brandRows->sum('cost'), 2), 'margin' => round($brandRows->sum('margin'), 2)];
        $payload = ['from' => $from, 'to' => $to, 'slow_days' => $slowDays, 'brands' => $brandRows, 'totals' => $tot,
            'cost_coverage_pct' => $lines ? round(($lines - $unknown) / $lines * 100) : 100, 'slow_moving' => $slow, 'staff' => $staff];

        if ($request->query('format') === 'csv') {
            return $this->csv("profit-$from-to-$to.csv", ['Brand', 'Units', 'Revenue', 'Cost', 'Margin', 'Margin %'],
                $brandRows->map(fn ($r) => array_values($r))->all());
        }

        return $this->data($payload);
    }
}
