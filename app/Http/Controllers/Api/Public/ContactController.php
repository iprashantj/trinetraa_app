<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'message' => 'required|string|max:2000',
        ]);

        $row = ContactEnquiry::create($data);

        return response()->json([
            'data' => $row,
            'message' => 'Thank you! Your message has been sent. We will get back to you soon.',
        ], 201);
    }
}
