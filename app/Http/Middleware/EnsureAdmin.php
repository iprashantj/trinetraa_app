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
