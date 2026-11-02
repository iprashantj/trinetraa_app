<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\EyeCheckupBill;
use App\Models\EyeTest;
use App\Models\FrameBill;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Customer profile + structured eye-test records. Prescription data is only returned to
 * users who hold the "prescriptions" permission (owners always do).
 */
class EyeTestController extends Controller
{
    private function fields(): array
    {
        $f = ['test_date' => ['required', 'regex:/^\d{4}-\d{2}-\d{2}$/'], 'pd' => 'nullable|string|max:12',
            'optometrist' => 'nullable|string|max:255', 'notes' => 'nullable|string|max:2000', 'branch_id' => 'nullable|integer|exists:branches,id',
            'report' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'];
        foreach (['right', 'left'] as $e) {
            foreach (['sph', 'cyl', 'axis', 'add', 'va'] as $k) {
                $f["{$e}_{$k}"] = 'nullable|string|max:12';
            }
        }

        return $f;
    }

    /** Profile: details + purchase history + invoices (+ eye tests when permitted). */
    public function profile(Request $request, $id)
    {
        $c = Customer::find($id);
        if (! $c) {
            return $this->notFound();
        }
        $admin = Access::admin($request);
        $canRx = Access::can($admin, 'prescriptions');
        $phone = preg_replace('/\D+/', '', (string) $c->phone);

        $bills = FrameBill::with('items')->where(function ($q) use ($c, $phone) {
            $q->where('customer_id', $c->id);
            if (strlen($phone) >= 10) {
                $q->orWhere('customer_contact', 'like', '%' . substr($phone, -10) . '%');
            }
        })->orderByDesc('bill_date')->limit(100)->get();
        $eyeBills = EyeCheckupBill::where('customer_id', $c->id)->orderByDesc('bill_date')->limit(100)->get(['id', 'bill_number', 'bill_date', 'total', 'balance_due']);
        $branches = Branch::pluck('name', 'id');

        $tests = $canRx
            ? EyeTest::where('customer_id', $c->id)->orderByDesc('test_date')->orderByDesc('id')->get()
                ->each(function ($t) use ($branches) {
                    $t->setAttribute('branch_name', $branches[$t->branch_id] ?? null);
                    $t->setAttribute('has_report', (bool) $t->report_path);
                    $t->makeHidden('report_path');
                })
            : [];

        return $this->data([
            'customer' => $c,
            'purchases' => $bills,
            'eye_bills' => $eyeBills,
            'eye_tests' => $tests,
            'prescriptions_visible' => $canRx,
            'total_spent' => (float) $bills->sum('total'),
        ]);
    }

    public function store(Request $request, $customerId)
    {
        if (! Customer::find($customerId)) {
            return $this->notFound();
        }
        $data = $request->validate($this->fields());
        $admin = Access::admin($request);
        $row = EyeTest::create($this->build($request, $data) + [
            'customer_id' => $customerId, 'created_by' => $admin['id'] ?? null,
            'branch_id' => Access::writeBranch($request, $data['branch_id'] ?? null) ?: null,
        ]);

        return $this->data($row->makeHidden('report_path'), null, 201);
    }

    public function update(Request $request, $id)
    {
        $row = EyeTest::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $data = $request->validate($this->fields());
        $row->update($this->build($request, $data, $row));

        return $this->data($row->makeHidden('report_path'));
    }

    public function destroy($id)
    {
        $row = EyeTest::find($id);
        if ($row) {
            if ($row->report_path) {
                Storage::disk('local')->delete($row->report_path);
            }
            $row->delete();
        }

        return $this->message('Deleted.');
    }

    /** Authenticated download — reports are not publicly reachable. */
    public function report(Request $request, $id)
    {
        $row = EyeTest::find($id);
        if (! $row || ! $row->report_path || ! Storage::disk('local')->exists($row->report_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($row->report_path, 'eye-test-' . $row->id . '.' . pathinfo($row->report_path, PATHINFO_EXTENSION));
    }

    private function build(Request $request, array $data, ?EyeTest $existing = null): array
    {
        $out = [];
        foreach (['test_date', 'pd', 'optometrist', 'notes'] as $k) {
            $out[$k] = $data[$k] ?? null;
        }
        foreach (['right', 'left'] as $e) {
            foreach (['sph', 'cyl', 'axis', 'add', 'va'] as $k) {
                $out["{$e}_{$k}"] = $data["{$e}_{$k}"] ?? null;
            }
        }
        $out['source'] = 'manual';
        if ($request->hasFile('report')) {
            if ($existing && $existing->report_path) {
                Storage::disk('local')->delete($existing->report_path);
            }
            $out['report_path'] = $request->file('report')->store('eye-reports', 'local');
            $out['source'] = 'upload';
        }

        return $out;
    }
}
