<?php

return [
    'super_admin' => [
        'name' => env('SUPER_ADMIN_NAME', 'Quản trị viên hệ thống'),
        'email' => env('SUPER_ADMIN_EMAIL', 'admin@shop.local'),
        'phone' => env('SUPER_ADMIN_PHONE', '0900000001'),
        'password' => env('SUPER_ADMIN_PASSWORD', 'password123'),
    ],
];
