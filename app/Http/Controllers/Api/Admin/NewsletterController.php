<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $page = (int) ($request->query('page', 1));
        $perPage = (int) ($request->query('per_page', 50));
        $skip = ($page - 1) * $perPage;

        $rows = NewsletterSubscriber::query()->orderByDesc('created_at')->skip($skip)->take($perPage)->get();
        $total = NewsletterSubscriber::query()->count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function destroy(Request $request)
    {
        $id = (int) $request->query('id');
        if (! $id) {
            return response()->json(['error' => 'id required'], 400);
        }

        $subscriber = NewsletterSubscriber::find($id);
        if ($subscriber) {
            $subscriber->delete();
        }

        return response()->json(['success' => true]);
    }
}
