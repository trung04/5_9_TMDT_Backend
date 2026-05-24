<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GhnClient;
use App\Support\LocalGhnLocationCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GhnLocationController extends Controller
{
    public function __construct(
        private readonly GhnClient $ghnClient,
        private readonly LocalGhnLocationCatalog $localCatalog,
    )
    {
    }

    public function provinces(): JsonResponse
    {
        $payload = $this->resolveLocations(
            'ghn.provinces',
            fn (): array => Cache::remember('ghn.provinces', now()->addDay(), fn (): array => $this->ghnClient->provinces()),
            fn (): array => $this->localCatalog->provinces(),
        );

        return response()->json([
            'message' => $payload['message'],
            'source' => $payload['source'],
            'data' => $payload['data'],
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => ['required', 'integer', 'min:1'],
        ]);
        $provinceId = (int) $validated['province_id'];
        $payload = $this->resolveLocations(
            "ghn.districts.{$provinceId}",
            fn (): array => Cache::remember(
                "ghn.districts.{$provinceId}",
                now()->addDay(),
                fn (): array => $this->ghnClient->districts($provinceId)
            ),
            fn (): array => $this->localCatalog->districts($provinceId),
        );

        return response()->json([
            'message' => $payload['message'],
            'source' => $payload['source'],
            'data' => $payload['data'],
        ]);
    }

    public function wards(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district_id' => ['required', 'integer', 'min:1'],
        ]);
        $districtId = (int) $validated['district_id'];
        $payload = $this->resolveLocations(
            "ghn.wards.{$districtId}",
            fn (): array => Cache::remember(
                "ghn.wards.{$districtId}",
                now()->addDay(),
                fn (): array => $this->ghnClient->wards($districtId)
            ),
            fn (): array => $this->localCatalog->wards($districtId),
        );

        return response()->json([
            'message' => $payload['message'],
            'source' => $payload['source'],
            'data' => $payload['data'],
        ]);
    }

    /**
     * @param  callable(): array  $resolver
     * @param  callable(): array  $fallback
     * @return array{message:string, source:string, data:array}
     */
    private function resolveLocations(string $cacheKey, callable $resolver, callable $fallback): array
    {
        try {
            $payload = $resolver();

            return [
                'message' => 'GHN location data retrieved successfully.',
                'source' => 'ghn',
                'data' => $payload['data'] ?? [],
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'message' => "Fallback location data retrieved successfully for {$cacheKey}.",
                'source' => 'local',
                'data' => $fallback(),
            ];
        }
    }
}
