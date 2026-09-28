<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'phone' => ['required', 'string', 'min:10', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'service' => 'required|in:eye-test,lens-fitting,frame-selection,consultation',
            'appt_date' => ['required', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'time_slot' => 'required|string|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $row = Appointment::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'service' => $data['service'],
            'appt_date' => $data['appt_date'],
            'time_slot' => $data['time_slot'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'data' => $row,
            'message' => "Appointment booked! We'll confirm shortly.",
        ], 201);
    }
}
