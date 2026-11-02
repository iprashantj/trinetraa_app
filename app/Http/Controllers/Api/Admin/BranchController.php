<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BranchController extends Controller
{
    private array $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:20|alpha_dash',
        'city' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:1000',
        'phone' => 'nullable|string|max:50',
        'email' => 'nullable|email|max:255',
        'hours' => 'nullable|string|max:255',
        'map_url' => 'nullable|string|max:500',
        'gstin' => ['nullable', 'string', 'max:20', 'regex:/^\d{2}[A-Z]{5}\d{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/i'],
        'invoice_prefix' => 'nullable|string|max:10|alpha_dash',
        'review_url' => 'nullable|url|max:500',
        'is_active' => 'sometimes|boolean',
    ];

    /** Branch staff only see their own branch; owners see all. */
    public function index(Request $request)
    {
        $admin = Access::admin($request);
        $q = Branch::query()->orderBy('name');
        if (! Access::isOwner($admin) && ! empty($admin['branch_id'])) {
            $q->where('id', $admin['branch_id']);
        }

        return $this->data($q->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules + []);
        $request->validate(['code' => 'unique:branches,code']);
        $row = Branch::create($data);
        Cache::forget('site_config');

        return $this->data($row, null, 201);
    }

    public function update(Request $request, $id)
    {
        $row = Branch::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $data = $request->validate($this->rules);
        $request->validate(['code' => 'unique:branches,code,' . $row->id]);
        $row->update($data);

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = Branch::find($id);
        if (! $row) {
            return $this->notFound();
        }
        if (\App\Models\StockLevel::where('branch_id', $row->id)->where('quantity', '>', 0)->exists()) {
            return $this->message('This branch still holds stock. Transfer it out first, or deactivate the branch instead.', 422);
        }
        $row->delete();

        return $this->message('Deleted.');
    }
}
