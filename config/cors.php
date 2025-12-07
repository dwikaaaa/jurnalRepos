<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:5174', 'http://127.0.0.1:5174', 'http://localhost:5173',
    'http://127.0.0.1:5173', 'https://oarless-nonfeebly-adelaide.ngrok-free.dev'], // React app URL

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];