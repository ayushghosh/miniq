<?php


    return [

        'default' => 'mysql',
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', 3306),
                'database' => env('DB_DATABASE', 'miniq'),
                'username' => env('DB_USERNAME', 'ayush'),
                'password' => env('DB_PASSWORD', 'secret'),
                'charset' => env('DB_CHARSET', 'utf8'),
                'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
                'prefix' => env('DB_PREFIX', ''),
                'timezone' => env('DB_TIMEZONE', '+00:00'),
                'strict' => env('DB_STRICT_MODE', false),
            ]
        ]
    ];