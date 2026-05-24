<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GhnClient
{
    public function isConfigured(bool $withShopId = false): bool
    {
        $token = trim((string) config('services.ghn.token'));
        $baseUrl = rtrim((string) config('services.ghn.base_url'), '/');

        if ($token === '' || $baseUrl === '') {
            return false;
        }

        if (! $withShopId) {
            return true;
        }

        return (int) config('services.ghn.shop_id') > 0;
    }

    public function provinces(): array
    {
        return $this->request('GET', '/master-data/province');
    }

    public function districts(int $provinceId): array
    {
        return $this->request('GET', '/master-data/district', [
            'province_id' => $provinceId,
        ]);
    }

    public function wards(int $districtId): array
    {
        return $this->request('GET', '/master-data/ward', [
            'district_id' => $districtId,
        ]);
    }

    public function createOrder(array $payload): array
    {
        return $this->request('POST', '/v2/shipping-order/create', $payload, true);
    }

    public function detailByClientOrderCode(string $clientOrderCode): array
    {
        return $this->request('POST', '/v2/shipping-order/detail-by-client-code', [
            'client_order_code' => $clientOrderCode,
        ]);
    }

    public function cancelOrders(array $orderCodes): array
    {
        return $this->request('POST', '/v2/switch-status/cancel', [
            'order_codes' => array_values($orderCodes),
        ], true);
    }

    private function request(string $method, string $path, array $payload = [], bool $withShopId = false): array
    {
        $token = trim((string) config('services.ghn.token'));
        $baseUrl = rtrim((string) config('services.ghn.base_url'), '/');

        if ($token === '' || $baseUrl === '') {
            throw new RuntimeException('GHN is not configured.');
        }

        $headers = [
            'Token' => $token,
            'Content-Type' => 'application/json',
        ];

        if ($withShopId) {
            $shopId = (int) config('services.ghn.shop_id');

            if ($shopId <= 0) {
                throw new RuntimeException('GHN shop id is not configured.');
            }

            $headers['ShopId'] = (string) $shopId;
        }

        $url = $baseUrl.'/'.ltrim($path, '/');
        $response = Http::withHeaders($headers)
            ->acceptJson()
            ->timeout(20)
            ->send($method, $url, $method === 'GET' ? ['query' => $payload] : ['json' => $payload]);

        if (! $response->successful()) {
            throw new RuntimeException($this->messageFromResponse($response->json(), $response->body()));
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw new RuntimeException('GHN returned an invalid response.');
        }

        $code = (int) ($body['code'] ?? 200);

        if ($code < 200 || $code >= 300) {
            throw new RuntimeException($this->messageFromResponse($body, 'GHN request failed.'));
        }

        return $body;
    }

    private function messageFromResponse(mixed $payload, string $fallback): string
    {
        if (is_array($payload)) {
            foreach (['code_message_value', 'message_display', 'message'] as $key) {
                if (isset($payload[$key]) && is_string($payload[$key]) && $payload[$key] !== '') {
                    return $payload[$key];
                }
            }
        }

        return $fallback !== '' ? $fallback : 'GHN request failed.';
    }
}
