<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',      // Desarrollo con Vite
        'http://127.0.0.1:5173',      // Desarrollo con Vite (127.0.0.1)
        'http://localhost:3000',      // Alternativo
        'https://impor-front.com',    // Producción frontend
        env('FRONTEND_URL', '*')      // Configurable desde .env
    ],
    // 'allowed_origins' => ['*'], // No se puede usar * con credentials
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization'],
    'max_age' => 0,
    'supports_credentials' => true,

];
