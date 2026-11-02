<?php

namespace App\Http\Middleware;

use App\Support\Access;
use Closure;
use Illuminate\Http\Request;

/** Usage: ->middleware('admin.perm:inventory'). Runs after admin.auth. */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (! Access::can(Access::admin($request), $permission)) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission for this.'], 403);
            }
            abort(403, 'You do not have permission to open this page.');
        }

        return $next($request);
    }
}
