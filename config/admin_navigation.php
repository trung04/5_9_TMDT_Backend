<?php

return [
    'sections' => [
        'overview' => 'Tổng quan',
        'commerce' => 'Quản lý bán hàng',
        'system' => 'Hệ thống',
    ],

    'modules' => [
        [
            'id' => 'dashboard',
            'label' => 'Tổng quan',
            'route' => 'admin-web.dashboard',
            'section' => 'overview',
        ],
        [
            'id' => 'users',
            'label' => 'Người dùng',
            'route' => 'admin-web.users.index',
            'section' => 'commerce',
        ],
        [
            'id' => 'products',
            'label' => 'Sản phẩm',
            'route' => 'admin-web.products.index',
            'section' => 'commerce',
        ],
        [
            'id' => 'categories',
            'label' => 'Danh mục',
            'route' => 'admin-web.categories.index',
            'section' => 'commerce',
        ],
        [
            'id' => 'suppliers',
            'label' => 'Nhà cung cấp',
            'route' => 'admin-web.suppliers.index',
            'section' => 'commerce',
        ],
        [
            'id' => 'shipping_carriers',
            'label' => 'Vận chuyển',
            'route' => 'admin-web.shipping-carriers.index',
            'section' => 'commerce',
        ],
        [
            'id' => 'logistics',
            'label' => 'Điều phối đơn',
            'route' => 'admin-web.orders.index',
            'section' => 'commerce',
        ],
        [
            'id' => 'community',
            'label' => 'Cộng đồng',
            'route' => 'admin-web.community.index',
            'section' => 'commerce',
        ],
        [
            'id' => 'settings',
            'label' => 'Cài đặt',
            'route' => 'admin-web.settings.show',
            'section' => 'system',
        ],
        [
            'id' => 'admins',
            'label' => 'Tài khoản admin',
            'route' => 'admin-web.admins.index',
            'section' => 'system',
        ],
    ],
];
