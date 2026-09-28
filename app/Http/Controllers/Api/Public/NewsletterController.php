<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
        ]);

        $email = strtolower(trim($data['email']));
        $name = $data['name'] ?? null;

        $existing = NewsletterSubscriber::where('email', $email)->first();
        if ($existing) {
            $update = ['is_active' => true];
            if ($name !== null) {
                $update['name'] = $name;
            }
            $existing->update($update);
        } else {
            NewsletterSubscriber::create([
                'email' => $email,
                'name' => $name,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscribed successfully!',
        ]);
    }
}
