<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EyeCheckupBill;
use App\Models\FrameBill;
use App\Support\SiteConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    private function idsFromRequest(Request $request): array
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))));
        if (! $ids) {
            abort(400, 'No bill ids given.');
        }

        return $ids;
    }

    public function frameBills(Request $request)
    {
        $ids = $this->idsFromRequest($request);

        $bills = FrameBill::with(['customer:id,name', 'items'])
            ->whereIn('id', $ids)
            ->orderByDesc('created_at')
            ->get();

        if ($bills->isEmpty()) {
            abort(404, 'No matching bills found.');
        }

        $pdf = Pdf::loadView('admin.pdf.frame-bill', [
            'bills' => $bills,
            'site' => SiteConfig::all(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('frame-bill-invoice.pdf');
    }

    public function eyeCheckupBills(Request $request)
    {
        $ids = $this->idsFromRequest($request);

        $bills = EyeCheckupBill::whereIn('id', $ids)
            ->orderByDesc('created_at')
            ->get();

        if ($bills->isEmpty()) {
            abort(404, 'No matching bills found.');
        }

        $pdf = Pdf::loadView('admin.pdf.eye-checkup-bill', [
            'bills' => $bills,
            'site' => SiteConfig::all(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('eye-checkup-bill-invoice.pdf');
    }
}
