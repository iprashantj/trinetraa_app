<?php

return [
    // Only the API surface needs CORS handling; Blade pages are same-origin.
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // No third-party origin should ever read cookie-authenticated API responses.
    // Restrict to this app's own origin instead of the framework's wildcard default.
    'allowed_origins' => [env('APP_URL', 'http://localhost')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Must stay false while allowed_origins is not '*': browsers reject
    // Access-Control-Allow-Credentials with a wildcard origin anyway, and the
    // JWT cookies here are never meant to be sent cross-site.
    'supports_credentials' => false,
];
