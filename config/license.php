<?php

return [

    /*
    |--------------------------------------------------------------------------
    | License enforcement (always on in buyer ERP builds)
    |--------------------------------------------------------------------------
    |
    | When true, unlicensed users are redirected to /license and cannot use the app.
    | Not configurable via .env — buyers must not bypass licensing by editing env.
    | (The separate license server project uses LICENSE_ENABLED=false there only.)
    |
    */
    'enabled' => true,

    /*
    | Product identifier — must match the value on your license server.
    */
    'product_code' => env('LICENSE_PRODUCT_CODE', 'FABLEAD_HOTEL'),

    /*
    | Product codes accepted by the license API (buyer ERP + license server).
    */
    'allowed_product_codes' => [
        'FABLEAD_ERP',
        'FABLEAD_HOTEL',
    ],

    /*
    | Human-readable names for product codes (license server admin UI).
    */
    'product_names' => [
        'FABLEAD_ERP' => 'ERP',
        'FABLEAD_HOTEL' => 'Hotel ERP',
    ],

    /*
    | Product code sent to the remote license API.
    |
    | Default to this app's own product_code so the license server can store and
    | display the correct product name per activated domain.
    | (Override in .env if you are using an older server that only accepts ERP.)
    */
    'remote_product_code' => env('LICENSE_REMOTE_PRODUCT_CODE', 'FABLEAD_HOTEL'),

    /*
    | HMAC secret shared between this app and your license server (min 32 chars).
    | Never commit the production value; ship it only via secure channels.
    */
    'secret' => env('LICENSE_SECRET', ''),

    /*
    | remote — verify/activate via LICENSE_SERVER_URL
    | offline — paste a signed license key (license:generate on author machine)
    */
    'mode' => env('LICENSE_MODE', 'remote'),

    'server_url' => rtrim(env('LICENSE_SERVER_URL', ''), '/'),

    'server_api_key' => env('LICENSE_SERVER_API_KEY', ''),

    /*
    | When LICENSE_SERVER_URL matches APP_URL, call license logic in-process
    | instead of HTTP (required for "php artisan serve" local testing).
    */
    'use_internal' => env('LICENSE_USE_INTERNAL', true),

    /*
    | Re-check remote license server every N minutes (0 = every HTTP request).
    | Default 1440 (24h). Use 2–5 only if you need suspend/revoke within minutes.
    */
    'verify_interval_minutes' => (int) env('LICENSE_VERIFY_INTERVAL_MINUTES', 1440),

    /*
    | @deprecated Use verify_interval_minutes. Kept for backward compatibility.
    */
    'verify_interval_hours' => (int) env('LICENSE_VERIFY_INTERVAL_HOURS', 0),

    /*
    | Hours to allow ERP use when license server is unreachable (0 = no grace period).
    */
    'grace_hours' => (int) env('LICENSE_GRACE_HOURS', 72),

    /*
    | Allow localhost / 127.0.0.1 without remote check when license type is development.
    */
    'allow_localhost' => env('LICENSE_ALLOW_LOCALHOST', true),

    /*
    | Domain limits per license type (author can override on server).
    */
    'domain_limits' => [
        'regular' => 1,
        'extended' => (int) env('LICENSE_EXTENDED_MAX_DOMAINS', 5),
        'development' => 1,
    ],

    /*
    | URI paths excluded from license middleware (no leading slash).
    */
    'except_paths' => [
        'license',
        'license/*',
        'up',
    ],

    /*
    | Buyer ERP: admin/API hosting disabled (use the separate license-server project).
    */
    'admin_enabled' => false,

    /*
    | License server admin credentials (only used when admin_enabled=true).
    */
    'admin_email' => env('LICENSE_ADMIN_EMAIL', 'admin@example.com'),
    'admin_password' => env('LICENSE_ADMIN_PASSWORD', ''),

    /*
    | Artisan commands that may run without a valid license.
    */
    'except_commands' => [
        'license:*',
        'migrate',
        'migrate:*',
        'db:seed',
        'db:seed:*',
        'key:generate',
        'config:clear',
        'cache:clear',
        'route:clear',
        'view:clear',
        'optimize:clear',
        'package:discover',
        'passport:install',
        'passport:keys',
        'storage:link',
    ],

];

