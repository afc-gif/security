<?php

return [
    'path' => '/',

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('COOKIE_SECURE', false),

    'http_only' => true,

    'same_site' => 'lax',
];
