<?php

return [
    // Rutas a las que aplica CORS
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://klyx-pi.vercel.app',  // Frontend en producción (Vercel)
        'http://localhost:5173',        // Desarrollo local Vite
        'http://localhost:5174',        // Desarrollo local Vite (puerto alternativo)
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    // false = autenticación con Bearer tokens (Sanctum API Tokens), sin cookies
    'supports_credentials' => false,
];
