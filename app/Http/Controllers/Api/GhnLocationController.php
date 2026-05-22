<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GhnClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GhnLocationController extends Controller
{
    public function __construct(private readonly GhnClient $ghnClient)
    {
    }

    public function provinces(): JsonResponse
    {
        $payload = Cache::remember('ghn.provinces', now()->addDay(), fn (): array => $this->ghnClient->provinces());

        return response()->json([
            'message' => 'GHN provinces retrieved successfully.',
            'data' => $payload['data'] ?? [],
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => ['required', 'integer', 'min:1'],
        ]);
        $provinceId = (int) $validated['province_id'];
        $payload = Cache::remember(
            "ghn.districts.{$provinceId}",
            now()->addDay(),
            fn (): array => $this->ghnClient->districts($provinceId)
        );

        return response()->json([
            'message' => 'GHN districts retrieved successfully.',
            'data' => $payload['data'] ?? [],
        ]);
    }

    public function wards(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district_id' => ['required', 'integer', 'min:1'],
        ]);
        $districtId = (int) $validated['district_id'];
        $payload = Cache::remember(
            "ghn.wards.{$districtId}",
            now()->addDay(),
            fn (): array => $this->ghnClient->wards($districtId)
        );

        return response()->json([
            'message' => 'GHN wards retrieved successfully.',
            'data' => $payload['data'] ?? [],
        ]);
    }
}
