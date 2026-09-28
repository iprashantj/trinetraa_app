<?php

namespace App\Http\Middleware;

use App\Support\Jwt;
use Closure;
use Illuminate\Http\Request;

class EnsureCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $decoded = Jwt::verify($request->cookie('customer_token'));

        if (! $decoded || ($decoded['type'] ?? null) !== 'customer') {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
            return redirect('/account/login');
        }

        $request->attributes->set('customer', $decoded);

        return $next($request);
    }
}
