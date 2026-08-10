<?php

return [
    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
        'root_folder' => env('CLOUDINARY_ROOT_FOLDER', 'security'),
    ],
    'whatsapp' => [
        'admin_number' => env('ADMIN_WHATSAPP_NUMBER', '09160450776'),
    ],
];
