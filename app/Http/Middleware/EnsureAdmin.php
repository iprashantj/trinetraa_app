<?php

namespace App\Http\Middleware;

use App\Support\Jwt;
use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $decoded = Jwt::verify($request->cookie('admin_token'));

        // Reject customer tokens; must reference a real, active admin user.
        $user = null;
        if ($decoded && ($decoded['type'] ?? 'admin') === 'admin' && isset($decoded['id'])) {
            $user = \App\Models\User::find($decoded['id']);
        }
        if (! $user || ! $user->is_active) {
            $decoded = null;
        } else {
            // Trust the database for role/identity, not the token.
            $decoded['role'] = $user->role ?? 'staff';
            $decoded['email'] = $user->email;
            $decoded['name'] = $user->name;
            $decoded['branch_id'] = $user->branch_id;
            $decoded['permissions'] = $user->permissions;
        }

        if (! $decoded) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
            return redirect('/admin/login');
        }

        $request->attributes->set('admin', $decoded);

        return $next($request);
    }
}
