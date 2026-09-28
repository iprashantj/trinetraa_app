<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

/**
 * Restricts a route to admins with the "owner" role.
 * Must run after EnsureAdmin (which sets the 'admin' request attribute).
 */
class EnsureOwner
{
    public function handle(Request $request, Closure $next)
    {
        $admin = $request->attributes->get('admin');

        $role = $admin['role'] ?? null;
        if ($role === null && isset($admin['id'])) {
            // Tokens issued before roles existed carry no role claim — fall back to the DB.
            $role = User::where('id', $admin['id'])->value('role');
        }

        if ($role !== 'owner') {
            return response()->json(['message' => 'Owner access required.'], 403);
        }

        return $next($request);
    }
}
