<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\FrameBill;
use App\Models\EyeCheckupBill;
use App\Support\SiteConfig;
use Barryvdh\DomPDF\Facade\Pdf;

/** Public store pages (one per branch) and tokenised invoice links. */
class StoreController extends Controller
{
    public function index()
    {
        return view('public.stores.index', ['branches' => Branch::where('is_active', true)->orderBy('name')->get()]);
    }

    public function show(string $code)
    {
        $branch = Branch::where('is_active', true)->whereRaw('LOWER(code) = ?', [strtolower($code)])->first();
        if (! $branch) {
            abort(404);
        }

        return view('public.stores.show', ['branch' => $branch, 'others' => Branch::where('is_active', true)->where('id', '!=', $branch->id)->orderBy('name')->get()]);
    }

    /** Customer-facing invoice PDF; the long random token is the only access control. */
    public function invoice(string $token)
    {
        if (strlen($token) < 20) {
            abort(404);
        }
        $site = SiteConfig::all();
        if ($bill = FrameBill::with(['customer:id,name', 'items'])->where('share_token', $token)->first()) {
            return Pdf::loadView('admin.pdf.frame-bill', ['bills' => collect([$bill]), 'site' => $site])->setPaper('a4', 'portrait')
                ->stream($bill->bill_number . '.pdf');
        }
        if ($bill = EyeCheckupBill::where('share_token', $token)->first()) {
            return Pdf::loadView('admin.pdf.eye-checkup-bill', ['bills' => collect([$bill]), 'site' => $site])->setPaper('a4', 'portrait')
                ->stream($bill->bill_number . '.pdf');
        }
        abort(404);
    }
}
