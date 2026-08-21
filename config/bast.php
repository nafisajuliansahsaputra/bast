<?php

return [
    'document' => [
        'code' => env(
            'BAST_DOCUMENT_CODE',
            'BAST',
        ),

        'institution_code' => env(
            'BAST_INSTITUTION_CODE',
            'DISKOMINFO',
        ),
    ],

    'super_admin' => [
        'email' => env(
            'BAST_SUPER_ADMIN_EMAIL',
        ),

        'password' => env(
            'BAST_SUPER_ADMIN_PASSWORD',
        ),
    ],

    'demo' => [
        'enabled' => env(
            'BAST_DEMO_MODE',
            false,
        ),

        'email' => env(
            'BAST_DEMO_EMAIL',
            'rina.maharani@bast.local',
        ),

        'read_only' => env(
            'BAST_DEMO_READ_ONLY',
            true,
        ),
    ],
];
