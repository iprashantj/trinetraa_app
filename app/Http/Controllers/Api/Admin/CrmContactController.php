<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use App\Models\CrmContact;
use Illuminate\Http\Request;

class CrmContactController extends Controller
{
    private const VALID_STATUSES = ['lead', 'prospect', 'customer', 'inactive'];

    public function index(Request $request)
    {
        $q = substr(trim((string) $request->query('q', '')), 0, 100);
        $status = $request->query('status', '');
        $page = max(1, (int) ($request->query('page') ?: 1));
        $limit = 20;

        if ($status && ! in_array($status, self::VALID_STATUSES, true)) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $query = CrmContact::query();
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }
        if ($status) {
            $query->where('status', $status);
        }

        $contacts = (clone $query)
            ->orderByDesc('updated_at')
            ->with(['activities' => fn ($a) => $a->orderByDesc('done_at')->take(3)])
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $total = $query->count();

        return response()->json(['contacts' => $contacts, 'total' => $total, 'page' => $page]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:100',
            'status' => 'sometimes|in:lead,prospect,customer,inactive',
            'notes' => 'nullable|string|max:2000',
        ]);

        $contact = CrmContact::create([
            'name' => $request->input('name'),
            'email' => $request->input('email') ?: null,
            'phone' => $request->input('phone') ?: null,
            'source' => $request->input('source') ?: null,
            'status' => $request->input('status', 'lead'),
            'notes' => $request->input('notes') ?: null,
        ]);

        return response()->json(['contact' => $contact], 201);
    }

    public function show(Request $request, $id)
    {
        $numId = $this->parseId($id);
        if (! $numId) {
            return response()->json(['message' => 'Invalid id'], 400);
        }

        $contact = CrmContact::with(['activities' => fn ($a) => $a->orderByDesc('done_at')])->find($numId);
        if (! $contact) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json(['contact' => $contact]);
    }

    public function update(Request $request, $id)
    {
        $numId = $this->parseId($id);
        if (! $numId) {
            return response()->json(['message' => 'Invalid id'], 400);
        }

        // Add activity branch
        if ($request->input('addActivity') === true) {
            $request->validate([
                'type' => 'required|in:call,email,visit,whatsapp,note',
                'description' => 'required|string|max:1000',
            ]);
            $activity = CrmActivity::create([
                'contact_id' => $numId,
                'type' => $request->input('type'),
                'description' => $request->input('description'),
            ]);

            return response()->json(['activity' => $activity]);
        }

        // Update contact fields
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:100',
            'status' => 'sometimes|in:lead,prospect,customer,inactive',
            'notes' => 'nullable|string|max:2000',
        ]);

        $allowed = ['name', 'email', 'phone', 'source', 'status', 'notes'];
        $data = [];
        foreach ($allowed as $key) {
            if ($request->has($key)) {
                $data[$key] = $request->input($key);
            }
        }

        if (count($data) === 0) {
            return response()->json(['message' => 'No valid fields'], 400);
        }

        $contact = CrmContact::find($numId);
        if (! $contact) {
            return $this->notFound();
        }
        $contact->update($data);

        return response()->json(['contact' => $contact]);
    }

    public function destroy(Request $request, $id)
    {
        $numId = $this->parseId($id);
        if (! $numId) {
            return response()->json(['message' => 'Invalid id'], 400);
        }
        $contact = CrmContact::find($numId);
        if ($contact) {
            $contact->delete();
        }

        return response()->json(['success' => true]);
    }

    private function parseId($id): ?int
    {
        $n = (int) $id;

        return $n <= 0 ? null : $n;
    }
}
