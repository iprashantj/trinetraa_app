<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

/**
 * Owner-only view of the admin audit trail.
 */
class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(10, (int) $request->query('per_page', 30)));

        $query = AdminActivityLog::query()->orderByDesc('created_at')->orderByDesc('id');
        if ($email = $request->query('user')) {
            $query->where('user_email', 'like', '%' . $email . '%');
        }

        $total = (clone $query)->count();
        $rows = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return $this->data($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }
}
