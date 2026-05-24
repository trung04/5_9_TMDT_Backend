<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GhnLocationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_endpoints_fall_back_to_local_catalog_when_ghn_is_not_configured(): void
    {
        config()->set('services.ghn.token', null);
        config()->set('services.ghn.shop_id', null);
        config()->set('services.ghn.base_url', null);

        $this->getJson('/api/shipping/ghn/provinces')
            ->assertOk()
            ->assertJsonPath('source', 'local')
            ->assertJsonPath('data.0.ProvinceName', 'Ha Noi');

        $this->getJson('/api/shipping/ghn/districts?province_id=201')
            ->assertOk()
            ->assertJsonPath('source', 'local')
            ->assertJsonPath('data.1.DistrictName', 'Cau Giay');

        $this->getJson('/api/shipping/ghn/wards?district_id=1454')
            ->assertOk()
            ->assertJsonPath('source', 'local')
            ->assertJsonPath('data.0.WardName', 'Dich Vong Hau');
    }

    public function test_location_endpoints_use_ghn_data_when_service_is_configured(): void
    {
        config()->set('services.ghn.token', 'test-token');
        config()->set('services.ghn.base_url', 'https://online-gateway.ghn.vn/shiip/public-api');

        Http::fake([
            'https://online-gateway.ghn.vn/shiip/public-api/master-data/province*' => Http::response([
                'code' => 200,
                'data' => [
                    [
                        'ProvinceID' => 999,
                        'ProvinceName' => 'Test Province',
                    ],
                ],
            ]),
        ]);

        $this->getJson('/api/shipping/ghn/provinces')
            ->assertOk()
            ->assertJsonPath('source', 'ghn')
            ->assertJsonPath('data.0.ProvinceID', 999)
            ->assertJsonPath('data.0.ProvinceName', 'Test Province');
    }
}
