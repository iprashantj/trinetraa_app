<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\NotifyMeRequest;
use Illuminate\Http\Request;

class NotifyMeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:100',
            'eyewear_id' => 'required|integer|min:1',
        ]);

        $existing = NotifyMeRequest::where('email', $data['email'])
            ->where('eyewear_id', $data['eyewear_id'])
            ->first();

        if ($existing) {
            return $this->message("You're already on the notify list for this product.", 409);
        }

        NotifyMeRequest::create($data);

        return response()->json([
            'message' => 'You will be notified when this product is back in stock!',
        ], 201);
    }
}
