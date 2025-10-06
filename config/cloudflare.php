<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration settings for the Cloudflare
    | API integration. You can set your API key, email, and other options
    | required to interact with the Cloudflare services.
    |
    */
    'driver' => env('CLOUDFLARE_DRIVER', 'api'),

    'username' => env('CLOUDFLARE_USERNAME', ''),

    'api_key' => env('CLOUDFLARE_API_KEY', ''),

    'api_token' => env('CLOUDFLARE_TOKEN', ''),

    'auth_strategy' => env('CLOUDFLARE_AUTH_STRATEGY', 'Bearer'),
];
