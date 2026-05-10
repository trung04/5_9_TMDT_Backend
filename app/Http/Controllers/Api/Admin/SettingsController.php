<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminSettingsRequest;
use App\Models\User;
use App\Services\AdminSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private readonly AdminSettingsService $settingsService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'message' => 'Admin settings retrieved successfully.',
            'data' => $this->settingsService->payload($user),
        ]);
    }

    public function update(UpdateAdminSettingsRequest $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $settings = $this->settingsService->update($user, $request->validated());

        return response()->json([
            'message' => 'Admin settings updated successfully.',
            'data' => $this->settingsService->payload($user->refresh()->setRelation('adminSetting', $settings)),
        ]);
    }

    private function ensureAdmin(Request $request): ?JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->role !== User::ROLE_ADMIN) {
            return response()->json([
                'message' => 'You are not allowed to access this resource.',
            ], 403);
        }

        if (! $user->canAuthenticate()) {
            return response()->json([
                'message' => 'Your account is not allowed to use this resource.',
            ], 403);
        }

        return null;
    }
}
