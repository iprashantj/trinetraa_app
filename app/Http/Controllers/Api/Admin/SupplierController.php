<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private array $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:50',
        'email' => 'nullable|email|max:255',
        'gstin' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:1000',
        'is_active' => 'sometimes|boolean',
    ];

    public function index()
    {
        return $this->data(Supplier::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        return $this->data(Supplier::create($request->validate($this->rules)), null, 201);
    }

    public function update(Request $request, $id)
    {
        $row = Supplier::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($request->validate($this->rules));

        return $this->data($row);
    }

    public function destroy($id)
    {
        Supplier::where('id', $id)->delete();

        return $this->message('Deleted.');
    }
}
