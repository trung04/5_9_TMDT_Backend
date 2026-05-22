<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    public function index(): JsonResponse
    {
        $regions = Region::query()
            ->where('is_active', true)
            ->withCount(['products' => function ($query): void {
                $query->where('is_active', true)->where('is_deleted', false);
            }])
            ->orderBy('id')
            ->get();

        return response()->json([
            'message' => 'Regions retrieved successfully.',
            'data' => $regions,
        ]);
    }
}
