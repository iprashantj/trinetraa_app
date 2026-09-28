<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    private array $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|string|max:500',
        'badge' => 'nullable|string|max:100',
        'valid_from' => 'nullable|string',
        'valid_to' => 'nullable|string',
        'sort_order' => 'sometimes|integer|min:0',
        'is_active' => 'sometimes|boolean',
    ];

    public function index()
    {
        $rows = Offer::query()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return $this->data($rows);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);
        $row = Offer::create($this->payload($request));

        return $this->data($row, null, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules);
        $row = Offer::find($id);
        if (! $row) {
            return $this->notFound();
        }
        $row->update($this->payload($request));

        return $this->data($row);
    }

    public function destroy($id)
    {
        $row = Offer::find($id);
        if ($row) {
            $row->delete();
        }

        return response()->json(['success' => true]);
    }

    private function payload(Request $request): array
    {
        $validFrom = $request->input('valid_from');
        $validTo = $request->input('valid_to');

        return [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image' => $request->input('image'),
            'badge' => $request->input('badge'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            'valid_from' => $validFrom ?: null,
            'valid_to' => $validTo ?: null,
        ];
    }
}
