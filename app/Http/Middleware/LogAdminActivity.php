<?php

namespace App\Http\Middleware;

use App\Models\AdminActivityLog;
use Closure;
use Illuminate\Http\Request;

/**
 * Audit trail: records every state-changing admin API call (who, what, when).
 * Runs after EnsureAdmin so the acting admin's identity is available.
 */
class LogAdminActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            try {
                $admin = $request->attributes->get('admin') ?? [];
                AdminActivityLog::create([
                    'user_id' => $admin['id'] ?? null,
                    'user_email' => $admin['email'] ?? null,
                    'method' => $request->method(),
                    'path' => '/' . ltrim($request->path(), '/'),
                    'action' => self::describe($request),
                    'status' => $response->getStatusCode(),
                    'ip' => $request->ip(),
                ]);
            } catch (\Throwable $e) {
                // The audit trail must never break the actual operation.
            }
        }

        return $response;
    }

    protected static function describe(Request $request): string
    {
        $verb = match ($request->method()) {
            'POST' => 'Created/submitted',
            'PUT', 'PATCH' => 'Updated',
            'DELETE' => 'Deleted',
            default => $request->method(),
        };
        // "api/admin/eyewears/12" → "eyewears #12"
        $segments = array_values(array_filter(explode('/', $request->path()), fn ($s) => ! in_array($s, ['api', 'admin'])));
        $resource = $segments[0] ?? 'resource';
        $id = isset($segments[1]) && is_numeric($segments[1]) ? " #{$segments[1]}" : '';

        return "$verb $resource$id";
    }
}
