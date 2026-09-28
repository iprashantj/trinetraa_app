<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Support\Captcha;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $page = (int) ($request->query('page', 1));
        $perPage = (int) ($request->query('per_page', 9));
        $skip = ($page - 1) * $perPage;
        $eyewearId = $request->query('eyewear_id');

        $query = Review::query()->where('is_approved', true);
        if ($eyewearId) {
            $query->where('eyewear_id', (int) $eyewearId);
        }

        $rows = (clone $query)->orderByDesc('created_at')->skip($skip)->take($perPage)->get();
        $total = $query->count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|string|email',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:2000',
            'image' => 'nullable|string|max:500|regex:/^\/uploads\/[a-zA-Z0-9\/_.-]+$/',
            'captcha_token' => 'required|string|min:1',
            'captcha_answer' => 'required|integer',
        ]);

        $userName = $this->stripHtml($data['user_name']);
        $reviewText = $this->stripHtml($data['review_text']);

        $existing = Review::where('user_email', $data['user_email'])->first();
        if ($existing) {
            return $this->message('A review with this email has already been submitted.', 422);
        }

        $wordCount = count(array_filter(preg_split('/\s+/', $reviewText)));
        if ($wordCount > 200) {
            return $this->message("Review must not exceed 200 words. Current: {$wordCount}.", 422);
        }

        $captchaOk = Captcha::verify($data['captcha_token'], $data['captcha_answer']);
        if (! $captchaOk) {
            return $this->message('The captcha answer is incorrect.', 422);
        }

        $row = Review::create([
            'user_name' => $userName,
            'user_email' => $data['user_email'],
            'rating' => $data['rating'],
            'review_text' => $reviewText,
            'image' => $data['image'] ?? null,
            'is_approved' => false,
        ]);

        return response()->json([
            'data' => $row,
            'message' => 'Thank you! Your review will appear after approval.',
        ], 201);
    }

    public function captcha()
    {
        return $this->data(Captcha::create());
    }

    private function stripHtml($s): string
    {
        return trim(preg_replace('/<[^>]*>/', '', (string) $s));
    }
}
