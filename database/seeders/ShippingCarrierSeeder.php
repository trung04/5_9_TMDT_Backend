<?php

namespace Database\Seeders;

use App\Models\ShippingCarrier;
use Illuminate\Database\Seeder;

class ShippingCarrierSeeder extends Seeder
{
    public function run(): void
    {
        ShippingCarrier::query()->updateOrCreate(
            ['code' => 'GHN'],
            [
                'name' => 'Giao Hang Nhanh',
                'provider' => ShippingCarrier::PROVIDER_GHN,
                'tracking_url_template' => 'https://donhang.ghn.vn/?order_code={code}',
                'default_weight' => 1000,
                'default_length' => 20,
                'default_width' => 20,
                'default_height' => 10,
                'default_service_type_id' => 2,
                'default_payment_type_id' => 1,
                'default_required_note' => 'KHONGCHOXEMHANG',
                'pickup_name' => config('app.name', 'Heritage Harvest'),
                'pickup_phone' => '0900000999',
                'pickup_address' => 'Kho chinh Ha Noi',
                'pickup_ward_name' => 'Phuong Dich Vong Hau',
                'pickup_district_name' => 'Quan Cau Giay',
                'pickup_province_name' => 'Ha Noi',
                'settings' => [
                    'shop_id_configured' => (bool) config('services.ghn.shop_id'),
                ],
                'is_active' => true,
                'is_deleted' => false,
            ],
        );

        ShippingCarrier::query()->updateOrCreate(
            ['code' => 'MANUAL'],
            [
                'name' => 'Van chuyen thu cong',
                'provider' => ShippingCarrier::PROVIDER_MANUAL,
                'tracking_url_template' => null,
                'default_weight' => 1000,
                'default_length' => 20,
                'default_width' => 20,
                'default_height' => 10,
                'default_service_type_id' => 2,
                'default_payment_type_id' => 1,
                'default_required_note' => 'KHONGCHOXEMHANG',
                'is_active' => true,
                'is_deleted' => false,
            ],
        );
    }
}
