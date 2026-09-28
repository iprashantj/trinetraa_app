<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\ProductEnquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'eyewear_id' => 'required|integer|min:1',
            'eyewear_name' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        ProductEnquiry::create($data);

        return response()->json([
            'message' => 'Enquiry submitted! We will contact you shortly.',
        ], 201);
    }
}
