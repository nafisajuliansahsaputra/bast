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
];
