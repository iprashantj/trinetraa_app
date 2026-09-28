<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\ServiceablePincode;
use Illuminate\Http\Request;

class CheckPincodeController extends Controller
{
    public function index(Request $request)
    {
        $pincode = trim((string) $request->query('pincode', ''));

        if (! $pincode || ! preg_match('/^\d{6}$/', $pincode)) {
            return response()->json([
                'serviceable' => false,
                'message' => 'Enter a valid 6-digit pincode.',
            ]);
        }

        $record = ServiceablePincode::where('pincode', $pincode)->first();

        if ($record && $record->is_active) {
            return response()->json([
                'serviceable' => true,
                'area' => $record->area,
                'message' => 'Delivery available to ' . ($record->area ?: $pincode) . '!',
            ]);
        }

        return response()->json([
            'serviceable' => false,
            'message' => "Sorry, we don't deliver to this pincode yet. Visit our store in Nashik!",
        ]);
    }
}
