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
                    'details' => self::details($request),
                ]);
            } catch (\Throwable $e) {
                // The audit trail must never break the actual operation.
            }
        }

        return $response;
    }

    /** Compact, secret-free summary of the submitted fields (prices, quantities, reasons...) for the audit trail. */
    protected static function details(Request $request): ?string
    {
        $skip = ['password', 'new_password', 'current_password', 'password_hash', 'code', 'totp_secret', 'token', '_token'];
        $data = [];
        foreach ($request->except($skip) as $k => $v) {
            if ($v instanceof \Illuminate\Http\UploadedFile) {
                $data[$k] = '[file]';
            } elseif (is_scalar($v) || $v === null) {
                $data[$k] = is_string($v) ? mb_substr($v, 0, 120) : $v;
            } else {
                $data[$k] = json_decode(mb_substr(json_encode($v), 0, 400)) ?? '[…]';
            }
        }

        return $data ? mb_substr(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 0, 1500) : null;
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
