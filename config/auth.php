<?php

return [


    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],



    // 'guards' => [
    //     'web' => [
    //         'driver' => 'session',
    //         'provider' => 'users',
    //     ],

    //     'api' => [
    //         'driver' => 'jwt',
    //         'provider' => 'users',
    //     ],
    // ],
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Add or modify api guard for JWT
        'api' => [
            'driver' => 'jwt', // tymon/jwt-auth driver
            'provider' => 'users',
            'hash' => false,
        ],
    ],




    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],


    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],


    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
