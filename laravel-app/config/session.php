<?php

return [
    'driver' => env('SESSION_DRIVER', 'file'),

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    'encrypt' => false,

    'files' => storage_path('framework/sessions'),

    'database' => env('SESSION_CONNECTION', null),

    'table' => 'sessions',

    'store' => null,

    'lottery' => [2, 100],

    'cookie' => env('SESSION_COOKIE', 'LARAVEL_SESSION'),

    'path' => '/',

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('SESSION_SECURE_COOKIES', false),

    'http_only' => true,

    'same_site' => 'lax',
];
