<?php

// config for Nikoleesg/LaravelHelpers
return [
    'onemap' => [
        'base_url' => env('ONEMAP_BASE_URL', 'https://www.onemap.gov.sg'),
        'email' => env('ONEMAP_EMAIL'),
        'password' => env('ONEMAP_PASSWORD'),
        'token' => env('ONEMAP_TOKEN'),
        'cache_key' => env('ONEMAP_CACHE_KEY', 'onemap_api_token'),
        'expiry_buffer' => env('ONEMAP_EXPIRY_BUFFER', 300),
        'timeout' => env('ONEMAP_TIMEOUT', 10),
    ],
];
