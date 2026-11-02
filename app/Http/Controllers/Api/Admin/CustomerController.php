<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
        'dob' => 'nullable|date|before:tomorrow',
        'marketing_consent' => 'sometimes|boolean',
        'whatsapp_opt_in' => 'sometimes|boolean',
    ];

    public function index(Request $request)
    {
        $page = max(1, (int) ($request->query('page') ?: 1));
        $perPage = max(1, min(500, (int) ($request->query('per_page') ?: 20)));
        $skip = ($page - 1) * $perPage;

        $search = trim((string) $request->query('search', ''));
        $base = Customer::query()->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%$search%")
            ->orWhere('phone', 'like', "%$search%")->orWhere('email', 'like', "%$search%")));
        $total = (clone $base)->count();
        $rows = $base->orderByDesc('created_at')->skip($skip)->take($perPage)->get();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $row = Customer::create($this->payload($request));

        return $this->data($row, null, 201);
    }

    public function show($id)
    {
        $row = Customer::find($id);
        if (! $row) {
            return $this->notFound();
        }

        return $this->data($row);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = Customer::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($this->payload($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = Customer::find($id);
        if ($row) {
            $row->delete();
        }

        return $this->message('Deleted.');
    }

    public function lookup()
    {
        $rows = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return $this->data($rows);
    }

    private function payload(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'dob' => $request->input('dob') ?: null,
        ] + $this->consent($request);
    }

    /** Marketing consent is recorded with the time it was given (SOW: consent recorded for marketing messages). */
    private function consent(Request $request): array
    {
        if (! $request->has('marketing_consent') && ! $request->has('whatsapp_opt_in')) {
            return [];
        }
        $yes = $request->boolean('marketing_consent');

        return [
            'marketing_consent' => $yes,
            'whatsapp_opt_in' => $request->boolean('whatsapp_opt_in'),
            'consent_at' => $yes ? now() : null,
        ];
    }
}
