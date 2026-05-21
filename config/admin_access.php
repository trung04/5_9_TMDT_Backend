<?php

return [
    'super_admin' => [
        'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
        'email' => env('SUPER_ADMIN_EMAIL', 'admin@shop.local'),
        'phone' => env('SUPER_ADMIN_PHONE', '0900000001'),
        'password' => env('SUPER_ADMIN_PASSWORD', 'password123'),
    ],

    'permissions' => [
        [
            'key' => 'admin.dashboard.view',
            'name' => 'View dashboard',
            'group' => 'Dashboard',
            'description' => 'View admin dashboard metrics and queues.',
        ],
        [
            'key' => 'admin.community.view',
            'name' => 'View community',
            'group' => 'Community',
            'description' => 'View community and supplier invitation data.',
        ],
        [
            'key' => 'admin.community.invitation.create',
            'name' => 'Create supplier invitations',
            'group' => 'Community',
            'description' => 'Create supplier invitation records.',
        ],
        [
            'key' => 'admin.settings.view',
            'name' => 'View admin settings',
            'group' => 'Settings',
            'description' => 'View admin settings for the current account.',
        ],
        [
            'key' => 'admin.settings.update',
            'name' => 'Update admin settings',
            'group' => 'Settings',
            'description' => 'Update admin settings for the current account.',
        ],
        [
            'key' => 'admin.products.view',
            'name' => 'View products',
            'group' => 'Catalog',
            'description' => 'View products in the admin catalog.',
        ],
        [
            'key' => 'admin.products.create',
            'name' => 'Create products',
            'group' => 'Catalog',
            'description' => 'Create products in the admin catalog.',
        ],
        [
            'key' => 'admin.products.update',
            'name' => 'Update products',
            'group' => 'Catalog',
            'description' => 'Update products and product status.',
        ],
        [
            'key' => 'admin.products.delete',
            'name' => 'Delete products',
            'group' => 'Catalog',
            'description' => 'Delete products when no related data exists.',
        ],
        [
            'key' => 'admin.categories.create',
            'name' => 'Create categories',
            'group' => 'Catalog',
            'description' => 'Create catalog categories.',
        ],
        [
            'key' => 'admin.categories.update',
            'name' => 'Update categories',
            'group' => 'Catalog',
            'description' => 'Update catalog categories.',
        ],
        [
            'key' => 'admin.categories.delete',
            'name' => 'Delete categories',
            'group' => 'Catalog',
            'description' => 'Delete catalog categories.',
        ],
        [
            'key' => 'admin.suppliers.create',
            'name' => 'Create suppliers',
            'group' => 'Catalog',
            'description' => 'Create supplier records.',
        ],
        [
            'key' => 'admin.suppliers.update',
            'name' => 'Update suppliers',
            'group' => 'Catalog',
            'description' => 'Update supplier records.',
        ],
        [
            'key' => 'admin.suppliers.delete',
            'name' => 'Delete suppliers',
            'group' => 'Catalog',
            'description' => 'Delete or deactivate supplier records.',
        ],
        [
            'key' => 'admin.orders.view',
            'name' => 'View orders',
            'group' => 'Orders',
            'description' => 'View admin order lists and order details.',
        ],
        [
            'key' => 'admin.orders.status.update',
            'name' => 'Update order status',
            'group' => 'Orders',
            'description' => 'Update fulfillment status for orders.',
        ],
        [
            'key' => 'admin.orders.payment.update',
            'name' => 'Update payment status',
            'group' => 'Orders',
            'description' => 'Update payment status for orders.',
        ],
        [
            'key' => 'admin.orders.bulk.update',
            'name' => 'Bulk update orders',
            'group' => 'Orders',
            'description' => 'Run bulk order status actions.',
        ],
    ],
];
