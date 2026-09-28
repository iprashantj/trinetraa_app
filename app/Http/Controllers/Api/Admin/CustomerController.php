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
    ];

    public function index(Request $request)
    {
        $page = (int) ($request->query('page') ?: 1);
        $perPage = (int) ($request->query('per_page') ?: 20);
        $skip = ($page - 1) * $perPage;

        $rows = Customer::query()
            ->orderByDesc('created_at')
            ->skip($skip)
            ->take($perPage)
            ->get();

        $total = Customer::count();

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
        ];
    }
}
