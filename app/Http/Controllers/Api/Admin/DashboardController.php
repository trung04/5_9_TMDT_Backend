<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Controller;
use App\Services\AdminInsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use EnsuresAdminAccess;

    public function __construct(private readonly AdminInsightService $adminInsightService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        return response()->json([
            'message' => 'Dashboard data retrieved successfully.',
            'data' => $this->adminInsightService->dashboardPayload(),
        ]);
    }
}
