<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotifyMeRequest;
use Illuminate\Http\Request;

class NotifyMeController extends Controller
{
    public function index()
    {
        $items = NotifyMeRequest::query()
            ->orderByDesc('created_at')
            ->take(100)
            ->with(['eyewear:id,name,slug'])
            ->get();
        $total = NotifyMeRequest::query()->count();

        return $this->data($items, ['total' => $total]);
    }

    public function update(Request $request)
    {
        $id = $request->input('id');
        $isNotified = $request->input('isNotified');

        $item = NotifyMeRequest::find($id);
        if (! $item) {
            return $this->notFound();
        }
        $item->update(['is_notified' => $isNotified]);

        return $this->data($item);
    }
}
