<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\FrameBill;
use App\Models\WorkOrder;
use App\Support\Access;
use App\Support\BillNumber;
use App\Support\Messenger;
use Illuminate\Http\Request;

/** Repair / lens-order / delivery tracking. Moving an order to "ready" tells the customer automatically. */
class WorkOrderController extends Controller
{
    private const FLOW = ['ordered' => 0, 'in_fitting' => 1, 'ready' => 2, 'delivered' => 3];

    private array $rules = [
        'customer_id' => 'nullable|integer|min:1',
        'frame_bill_id' => 'nullable|integer|min:1',
        'branch_id' => 'nullable|integer|min:1',
        'customer_name' => 'nullable|string|max:255',
        'customer_contact' => 'nullable|string|max:40',
        'description' => 'nullable|string|max:500',
        'expected_date' => 'nullable|date',
        'notes' => 'nullable|string',
    ];

    public function index(Request $request)
    {
        $branchId = Access::branchScope($request);
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $page = max(1, (int) ($request->query('page') ?: 1));
        $perPage = max(1, min(200, (int) ($request->query('per_page') ?: 25)));

        $base = WorkOrder::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('order_number', 'like', "%$search%")
                ->orWhere('customer_name', 'like', "%$search%")->orWhere('customer_contact', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%")));
        $counts = (clone $base)->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
        $total = (clone $base)->when($status !== '', fn ($q) => $q->where('status', $status))->count();
        $rows = $base->when($status !== '', fn ($q) => $q->where('status', $status))->orderByDesc('id')
            ->skip(($page - 1) * $perPage)->take($perPage)->get();
        $branches = Branch::pluck('name', 'id');
        $rows->each(fn ($r) => $r->setAttribute('branch_name', $branches[$r->branch_id] ?? null));

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'counts' => $counts]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $admin = Access::admin($request);
        $data = $request->only(array_keys($this->rules));
        $branch = Access::writeBranch($request, $data['branch_id'] ?? null);

        if (! empty($data['frame_bill_id'])) {
            $bill = FrameBill::find($data['frame_bill_id']);
            if (! $bill) {
                return $this->message('That bill was not found.', 422);
            }
            if (! Access::canUseBranch($admin, (int) $bill->branch_id)) {
                return $this->message('That bill belongs to another branch.', 403);
            }
            $data['customer_id'] = $data['customer_id'] ?? $bill->customer_id;
            $data['customer_name'] = $data['customer_name'] ?? $bill->customer_name;
            $data['customer_contact'] = $data['customer_contact'] ?? $bill->customer_contact;
            $branch = $branch ?: (int) $bill->branch_id;
        }
        if (! empty($data['customer_id']) && ($c = Customer::find($data['customer_id']))) {
            $data['customer_name'] = $data['customer_name'] ?? $c->name;
            $data['customer_contact'] = $data['customer_contact'] ?? $c->phone;
        }
        if (! $branch || ! Branch::find($branch)) {
            return $this->message('Choose a branch for this order.', 422);
        }
        if (empty($data['customer_name'])) {
            return $this->message('Enter the customer name.', 422);
        }

        $row = WorkOrder::create($data + [
            'order_number' => BillNumber::generate('WO'), 'branch_id' => $branch, 'status' => 'ordered', 'created_by' => $admin['id'] ?? null,
        ]);

        return $this->data($row, null, 201);
    }

    public function show(Request $request, $id)
    {
        $row = $this->find($request, $id);

        return $row instanceof WorkOrder ? $this->data($row) : $row;
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = $this->find($request, $id);
        if (! $row instanceof WorkOrder) {
            return $row;
        }
        $row->update($request->only(['customer_name', 'customer_contact', 'description', 'expected_date', 'notes']));

        return $this->data($row->fresh());
    }

    /** Forward-only flow (ordered → in_fitting → ready → delivered); cancel allowed until delivered. */
    public function setStatus(Request $request, $id)
    {
        $to = $request->validate(['status' => 'required|in:' . implode(',', WorkOrder::STATUSES)])['status'];
        $row = $this->find($request, $id);
        if (! $row instanceof WorkOrder) {
            return $row;
        }
        $from = $row->status;
        if (in_array($from, ['delivered', 'cancelled'], true)) {
            return $this->message("This order is already {$from}.", 409);
        }
        if ($to === $from) {
            return $this->data($row);
        }
        if ($to !== 'cancelled' && (self::FLOW[$to] ?? -1) < (self::FLOW[$from] ?? 0)) {
            return $this->message("Cannot move an order back from {$from} to {$to}.", 409);
        }

        $patch = ['status' => $to];
        if ($to === 'ready') {
            $patch['ready_at'] = now();
        }
        if ($to === 'delivered') {
            $patch['delivered_at'] = now();
            $patch['ready_at'] = $row->ready_at ?? now();
        }
        $row->update($patch);

        $notify = null;
        if ($to === 'ready') {
            $notify = $this->notifyCustomer($row);
        }

        return $this->data($row->fresh(), $notify ? ['notify' => $notify] : null);
    }

    public function renotify(Request $request, $id)
    {
        $row = $this->find($request, $id);
        if (! $row instanceof WorkOrder) {
            return $row;
        }
        if ($row->status !== 'ready') {
            return $this->message('Only orders that are ready can be re-notified.', 409);
        }

        return $this->data($row->fresh(), ['notify' => $this->notifyCustomer($row)]);
    }

    public function destroy(Request $request, $id)
    {
        $row = $this->find($request, $id);
        if ($row instanceof WorkOrder) {
            $row->delete();

            return $this->message('Deleted.');
        }

        return $row;
    }

    /** Transactional message (no marketing consent needed). Returns wa.me / sms: links when no provider sent it. */
    private function notifyCustomer(WorkOrder $row): array
    {
        $branch = Branch::find($row->branch_id);
        $msg = 'Hello ' . ($row->customer_name ?: 'Customer') . ', your order ' . $row->order_number . ' is ready for pickup at Trinetraa Optician'
            . ($branch ? ' ' . $branch->name : '') . '.' . ($branch && $branch->phone ? ' Call us on ' . $branch->phone . ' for any help.' : '') . ' Thank you!';
        $customer = $row->customer_id ? Customer::find($row->customer_id) : null;
        $r = Messenger::notify($msg, [
            'customer' => $customer, 'phone' => $row->customer_contact, 'email' => $customer?->email,
            'type' => 'work_order_ready', 'subject' => 'Your order ' . $row->order_number . ' is ready', 'transactional' => true,
        ]);
        if ($r['sent']) {
            $row->update(['notified_at' => now()]);
        }

        return $r + ['message' => $msg];
    }

    private function find(Request $request, $id)
    {
        $row = WorkOrder::find($id);
        if (! $row) {
            return $this->notFound();
        }
        if (! Access::canUseBranch(Access::admin($request), (int) $row->branch_id)) {
            return $this->message('This order belongs to another branch.', 403);
        }

        return $row;
    }
}
