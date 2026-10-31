<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Visitor analytics
    |--------------------------------------------------------------------------
    | Page views on the public site are recorded by App\Http\Middleware\TrackVisit
    | and shown on the admin dashboard. Raw IP addresses are never stored: each
    | visitor is identified by a hash that changes every day.
    */

    'enabled' => env('ANALYTICS_ENABLED', true),

    /*
    | Where visitor locations come from:
    |   'ip-api' - Cloudflare's CF-IPCountry / CF-IPCity headers when present,
    |              otherwise a lookup on ip-api.com (free, no key, sends the IP).
    |   'headers' - only Cloudflare headers, nothing leaves the server.
    |   'none'   - don't record locations.
    */

    'geo' => env('ANALYTICS_GEO', 'ip-api'),

    // Visits older than this many months are deleted by `php artisan model:prune`.
    'keep_months' => (int) env('ANALYTICS_KEEP_MONTHS', 13),

];
