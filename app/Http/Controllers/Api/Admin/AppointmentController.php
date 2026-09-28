<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $page = (int) ($request->query('page', 1));
        $perPage = (int) ($request->query('per_page', 20));
        $skip = ($page - 1) * $perPage;

        $rows = Appointment::query()->orderByDesc('created_at')->skip($skip)->take($perPage)->get();
        $total = Appointment::query()->count();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $appointment = Appointment::find($id);
        if (! $appointment) {
            return $this->notFound();
        }
        $appointment->update(['status' => $data['status']]);

        return $this->data($appointment);
    }
}
