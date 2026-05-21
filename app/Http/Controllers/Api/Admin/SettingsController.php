<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminSettingsRequest;
use App\Models\User;
use App\Services\AdminSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use EnsuresAdminAccess;

    public function __construct(private readonly AdminSettingsService $settingsService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.settings.view')) {
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
        if ($response = $this->ensureAdmin($request, 'admin.settings.update')) {
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

}
