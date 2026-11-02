<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillReturn;
use App\Models\BillReturnItem;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\EyeCheckupBill;
use App\Models\FrameBill;
use App\Support\Access;
use App\Support\BillNumber;
use App\Support\ResendMailer;
use App\Support\SiteConfig;
use App\Support\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Payment, invoice-sharing, returns and exchanges for frame bills (the POS bills). */
class BillingController extends Controller
{
    private function bill($id): ?FrameBill
    {
        return FrameBill::with(['items', 'customer:id,name,email,phone'])->find($id);
    }

    private function ensureToken(FrameBill $b): string
    {
        if (! $b->share_token) {
            $b->update(['share_token' => Str::random(40)]);
        }

        return $b->share_token;
    }

    private function phone(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        if (strlen($d) === 10) {
            return '91' . $d;
        }

        return strlen($d) >= 11 ? $d : null;
    }

    /** UPI deep link (rendered as a QR in the browser) so the customer pays straight into the shop account. */
    public function upi($id)
    {
        $b = $this->bill($id);
        if (! $b) {
            return $this->notFound();
        }
        $site = SiteConfig::all();
        if (empty($site['upiId'])) {
            return $this->message('Add the shop UPI ID in Settings → Payments first.', 422);
        }
        $due = max(0, (float) $b->total - (float) ($b->paid_amount ?? 0));
        $uri = 'upi://pay?' . http_build_query([
            'pa' => $site['upiId'], 'pn' => $site['upiName'] ?: 'Trinetraa Optician',
            'am' => number_format($due ?: (float) $b->total, 2, '.', ''), 'cu' => 'INR', 'tn' => $b->bill_number,
        ], '', '&', PHP_QUERY_RFC3986);

        return $this->data(['uri' => $uri, 'amount' => $due ?: (float) $b->total, 'payee' => $site['upiName'] ?: 'Trinetraa Optician']);
    }

    /** Staff confirms the money arrived (UPI app / card machine / cash). */
    public function markPaid(Request $request, $id)
    {
        $data = $request->validate(['payment_method' => 'required|in:cash,upi,card,online']);
        $b = FrameBill::find($id);
        if (! $b) {
            return $this->notFound();
        }
        $b->update(['payment_method' => $data['payment_method'], 'payment_status' => 'paid', 'paid_amount' => $b->total]);
        \App\Support\Pricing::earnForBill($b->fresh('customer'));

        return $this->data($b->fresh());
    }

    /** Optional Razorpay payment link — only active when RAZORPAY_KEY_ID / RAZORPAY_KEY_SECRET are set. */
    public function paymentLink($id)
    {
        $key = env('RAZORPAY_KEY_ID');
        $secret = env('RAZORPAY_KEY_SECRET');
        if (! $key || ! $secret) {
            return $this->message('Online card payments are not configured. Add RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET to the server settings.', 422);
        }
        $b = $this->bill($id);
        if (! $b) {
            return $this->notFound();
        }
        $due = max(0, (float) $b->total - (float) ($b->paid_amount ?? 0));
        $phone = $this->phone($b->customer_contact ?? $b->customer->phone ?? null);
        $res = Http::withBasicAuth($key, $secret)->timeout(20)->post('https://api.razorpay.com/v1/payment_links', array_filter([
            'amount' => (int) round($due * 100), 'currency' => 'INR', 'reference_id' => $b->bill_number,
            'description' => 'Invoice ' . $b->bill_number . ' — Trinetraa Optician',
            'customer' => array_filter(['name' => $b->customer_name, 'contact' => $phone ? '+' . $phone : null]),
        ]));
        if (! $res->successful()) {
            return $this->message('Payment gateway error: ' . ($res->json('error.description') ?? 'request failed'), 502);
        }
        $b->update(['payment_link_id' => $res->json('id'), 'payment_link_url' => $res->json('short_url'), 'payment_method' => 'online']);

        return $this->data(['url' => $b->payment_link_url]);
    }

    public function paymentStatus($id)
    {
        $b = FrameBill::find($id);
        if (! $b || ! $b->payment_link_id) {
            return $this->notFound('No online payment link for this bill.');
        }
        $res = Http::withBasicAuth(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'))->timeout(20)
            ->get('https://api.razorpay.com/v1/payment_links/' . $b->payment_link_id);
        if (! $res->successful()) {
            return $this->message('Could not check the payment status.', 502);
        }
        if ($res->json('status') === 'paid') {
            $b->update(['payment_status' => 'paid', 'paid_amount' => $b->total]);
            \App\Support\Pricing::earnForBill($b->fresh('customer'));
        }

        return $this->data(['status' => $res->json('status'), 'bill' => $b->fresh()]);
    }

    /** Links for WhatsApp / SMS / email / PDF. WhatsApp and SMS open the staff member's own app with the message ready. */
    public function share($id)
    {
        $b = $this->bill($id);
        if (! $b) {
            return $this->notFound();
        }
        $site = SiteConfig::all();
        $url = $site['siteUrl'] . '/invoice/' . $this->ensureToken($b);
        $msg = "Hello " . ($b->customer_name ?: 'Customer') . ", thank you for shopping at Trinetraa Optician. Your invoice {$b->bill_number} for ₹" . number_format((float) $b->total, 2) . " is here: {$url}";
        $phone = $this->phone($b->customer_contact ?? $b->customer->phone ?? null);
        $email = $b->customer->email ?? null;

        return $this->data([
            'pdf_url' => $url,
            'message' => $msg,
            'whatsapp_url' => $phone ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($msg) : null,
            'sms_url' => $phone ? 'sms:+' . $phone . '?body=' . rawurlencode($msg) : null,
            'email' => $email,
        ]);
    }

    public function email(Request $request, $id)
    {
        $data = $request->validate(['to' => 'nullable|email']);
        $b = $this->bill($id);
        if (! $b) {
            return $this->notFound();
        }
        $to = $data['to'] ?? ($b->customer->email ?? null);
        if (! $to) {
            return $this->message('No email address for this customer.', 422);
        }
        $site = SiteConfig::all();
        $url = $site['siteUrl'] . '/invoice/' . $this->ensureToken($b);
        try {
            ResendMailer::send($to, "Your invoice {$b->bill_number} — Trinetraa Optician",
                '<p>Hello ' . e($b->customer_name ?: 'Customer') . ',</p><p>Thank you for shopping at Trinetraa Optician. Your invoice <strong>' . e($b->bill_number) . '</strong> for <strong>₹' . number_format((float) $b->total, 2) . '</strong> is ready.</p><p><a href="' . e($url) . '">View / download invoice (PDF)</a></p>');
        } catch (\Throwable $e) {
            return $this->message('Email could not be sent: ' . $e->getMessage(), 502);
        }

        return $this->message('Invoice emailed to ' . $to . '.');
    }

    /** Same share links as frame bills, for eye-checkup bills (contact is stored on the bill itself). */
    public function shareEyeCheckup($id)
    {
        $b = EyeCheckupBill::find($id);
        if (! $b) {
            return $this->notFound();
        }
        if (! $b->share_token) {
            $b->update(['share_token' => Str::random(40)]);
        }
        $site = SiteConfig::all();
        $url = $site['siteUrl'] . '/invoice/' . $b->share_token;
        $msg = "Hello " . ($b->customer_name ?: 'Customer') . ", thank you for visiting Trinetraa Optician. Your invoice {$b->bill_number} for ₹" . number_format((float) $b->total, 2) . " is here: {$url}";
        $phone = $this->phone($b->customer_contact);

        return $this->data([
            'pdf_url' => $url, 'message' => $msg,
            'whatsapp_url' => $phone ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($msg) : null,
            'sms_url' => $phone ? 'sms:+' . $phone . '?body=' . rawurlencode($msg) : null,
        ]);
    }

    // ── Returns & exchanges ───────────────────────────────────────────────────
    public function returns(Request $request)
    {
        $branchId = Access::branchScope($request);
        $rows = BillReturn::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('id')->limit(200)->get();
        $bills = FrameBill::whereIn('id', $rows->pluck('frame_bill_id'))->pluck('bill_number', 'id');
        $rows->each(fn ($r) => $r->setAttribute('bill_number', $bills[$r->frame_bill_id] ?? '—'));

        return $this->data($rows);
    }

    public function storeReturn(Request $request)
    {
        $data = $request->validate([
            'frame_bill_id' => 'required|integer|exists:frame_bills,id',
            'type' => 'required|in:return,exchange',
            'reason' => 'required|string|min:3|max:255',
            'refund_amount' => 'nullable|numeric|min:0',
            'refund_method' => 'nullable|in:cash,upi,card,online,credit_note',
            'items' => 'required|array|min:1',
            'items.*.frame_bill_item_id' => 'required|integer|exists:frame_bill_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.restock' => 'sometimes|boolean',
        ]);
        $bill = FrameBill::with('items')->find($data['frame_bill_id']);
        $admin = Access::admin($request);
        if ($bill->branch_id && ! Access::canUseBranch($admin, (int) $bill->branch_id)) {
            return $this->message('This bill belongs to another branch.', 403);
        }

        $return = DB::transaction(function () use ($data, $bill, $admin) {
            $r = BillReturn::create([
                'return_number' => BillNumber::generate('RT'), 'frame_bill_id' => $bill->id, 'branch_id' => $bill->branch_id,
                'type' => $data['type'], 'reason' => $data['reason'], 'refund_amount' => (float) ($data['refund_amount'] ?? 0),
                'refund_method' => $data['refund_method'] ?? null, 'created_by' => $admin['id'] ?? null,
            ]);
            foreach ($data['items'] as $i => $line) {
                $item = $bill->items->firstWhere('id', $line['frame_bill_item_id']);
                if (! $item) {
                    throw ValidationException::withMessages(["items.$i.frame_bill_item_id" => 'That line is not on this bill.']);
                }
                $already = (int) BillReturnItem::where('frame_bill_item_id', $item->id)->sum('quantity');
                if ($line['quantity'] + $already > $item->quantity) {
                    throw ValidationException::withMessages(["items.$i.quantity" => 'More than was sold (' . ($item->quantity - $already) . ' left to return).']);
                }
                $restock = $line['restock'] ?? true;
                BillReturnItem::create(['bill_return_id' => $r->id, 'frame_bill_item_id' => $item->id, 'quantity' => $line['quantity'], 'restock' => $restock]);
                if ($restock && $item->stock_item_id && $bill->branch_id) {
                    StockService::restock($item->stock_item_id, $bill->branch_id, (int) $line['quantity'], 'return', "{$data['type']}: {$data['reason']} ({$r->return_number})", 'bill_return', $r->id, $admin['id'] ?? null);
                }
            }
            if ($r->refund_amount > 0 && $data['type'] === 'return') {
                $remaining = (float) $bill->paid_amount - $r->refund_amount;
                $bill->update(['paid_amount' => max(0, $remaining), 'payment_status' => $remaining <= 0 ? 'refunded' : 'partial']);
            }

            return $r;
        });

        return $this->data($return->load([]), null, 201);
    }
}
