<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ETA Delay Threshold (Hours)
    |--------------------------------------------------------------------------
    | Threshold in hours used to flag significant arrival delays.
    */
    'delay_threshold_hours' => (float) env('CARGOTRACE_DELAY_THRESHOLD_HOURS', 12),

    /*
    |--------------------------------------------------------------------------
    | External APIs
    |--------------------------------------------------------------------------
    */
    'openweathermap' => [
        'api_key' => env('OPENWEATHERMAP_API_KEY', ''),
        'base_url' => env('OPENWEATHERMAP_BASE_URL', 'https://api.openweathermap.org/data/2.5'),
        'timeout' => 5,
    ],

    'exchangerate' => [
        'api_key' => env('EXCHANGERATE_API_KEY', ''),
        'base_url' => env('EXCHANGERATE_BASE_URL', 'https://v6.exchangerate-api.com/v6'),
        'timeout' => 5,
        'supported_currencies' => ['USD', 'EUR', 'SGD', 'AUD', 'IDR'],
    ],
];

