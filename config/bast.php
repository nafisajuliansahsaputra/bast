<?php

return [
    'super_admin' => [
        'email' => env(
            'BAST_SUPER_ADMIN_EMAIL',
            'admin@bast.test',
        ),

        'password' => env(
            'BAST_SUPER_ADMIN_PASSWORD',
            'BastAdmin123!',
        ),
    ],
];
