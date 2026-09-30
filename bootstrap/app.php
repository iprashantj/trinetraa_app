<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureCustomer;
use App\Http\Middleware\EnsureOwner;
use App\Http\Middleware\LogAdminActivity;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Our JWT/session cookies are plain (not Laravel-encrypted) so the
        // API routes (no EncryptCookies) and web routes agree on their value.
        $middleware->encryptCookies(except: [
            'admin_token',
            'customer_token',
            'cust_session',
        ]);

        $middleware->alias([
            'admin.auth' => EnsureAdmin::class,
            'admin.owner' => EnsureOwner::class,
            'admin.log' => LogAdminActivity::class,
            'customer.auth' => EnsureCustomer::class,
        ]);

        $middleware->web(append: [SecurityHeaders::class, TrackVisit::class]);
        $middleware->api(append: [SecurityHeaders::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Mirror the Next.js apiError.js validation shape: { message, errors }.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                $errors = [];
                foreach ($e->errors() as $field => $messages) {
                    $errors[$field] = $messages[0] ?? 'Invalid value.';
                }
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => $errors,
                ], 422);
            }
        });
    })->create();
