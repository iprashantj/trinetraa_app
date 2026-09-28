<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceablePincode;
use Illuminate\Http\Request;

class PincodeController extends Controller
{
    public function index()
    {
        $items = ServiceablePincode::query()->orderBy('pincode')->get();

        return $this->data($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pincode' => 'required|string|min:4|max:10',
            'area' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $item = ServiceablePincode::create($data);

        return $this->data($item, null, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'pincode' => 'sometimes|string|min:4|max:10',
            'area' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $item = ServiceablePincode::find($id);
        if (! $item) {
            return $this->notFound();
        }
        $item->update($data);

        return $this->data($item);
    }

    public function destroy($id)
    {
        $item = ServiceablePincode::find($id);
        if ($item) {
            $item->delete();
        }

        return $this->message('Deleted');
    }
}
