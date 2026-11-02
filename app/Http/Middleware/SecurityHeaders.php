<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(self), payment=()');

        if (!$request->is('api/*')) {
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
                "font-src 'self' https://fonts.gstatic.com data:; " .
                "img-src 'self' data: https: blob:; " .
                "media-src 'self' https://videos.pexels.com; " .
                "connect-src 'self' https://cdn.jsdelivr.net; " .
                "frame-src https://maps.google.com https://www.google.com; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self'; " .
                "frame-ancestors 'self';"
            );
        }

        // Only meaningful (and only sent) over HTTPS, so local http development is unaffected.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=15552000');
        }

        return $response;
    }
}
