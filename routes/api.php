<?php

/*
|--------------------------------------------------------------------------
| API Routes — ported 1:1 from the Next.js app/api/** route handlers.
|--------------------------------------------------------------------------
| Each domain lives in its own partial under routes/api/ and is auto-loaded
| here. All routes are prefixed with /api by the framework.
*/

foreach (glob(__DIR__ . '/api/*.php') as $partial) {
    require $partial;
}
