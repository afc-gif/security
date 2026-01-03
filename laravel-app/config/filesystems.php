<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'path' => storage_path('app'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'private',
        ],

        'public' => [
            'driver' => 'local',
            'path' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
