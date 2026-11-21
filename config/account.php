<?php

return [

    'admin' => [
        'name'     => env('ADMIN_NAME', 'System Administrator'),
        'email'    => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'homeowner' => [
        'password' => env('HOMEOWNER_PASSWORD'),
    ]
];
