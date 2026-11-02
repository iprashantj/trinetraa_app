<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $page = max(1, (int) ($request->query('page', 1)));
        $perPage = max(1, min(500, (int) ($request->query('per_page', 20))));
        $skip = ($page - 1) * $perPage;

        $rows = Review::query()->orderByDesc('created_at')->skip($skip)->take($perPage)->get();
        $total = Review::query()->count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function approve($id)
    {
        $review = Review::find($id);
        if (! $review) {
            return $this->notFound();
        }
        $review->update(['is_approved' => true]);

        return $this->data($review);
    }

    public function destroy($id)
    {
        $review = Review::find($id);
        if ($review) {
            $review->delete();
        }

        return $this->message('Deleted.');
    }
}
