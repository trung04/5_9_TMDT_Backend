<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait EnsuresAdminAccess
{
    private function ensureAdmin(Request $request, ?string $permissionKey = null): ?JsonResponse
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
                'message' => 'You do not have permission to access this resource.',
            ], 403);
        }

        if (! $user->canAuthenticate()) {
            return response()->json([
                'message' => 'Your account is not allowed to use this resource.',
            ], 403);
        }

        $user->loadMissing(['adminRole.permissions']);

        if (! $user->adminRole) {
            return response()->json([
                'message' => 'Your admin account has not been assigned a role.',
            ], 403);
        }

        if ($permissionKey !== null && ! $user->hasAdminPermission($permissionKey)) {
            return response()->json([
                'message' => 'You do not have permission to access this resource.',
            ], 403);
        }

        return null;
    }

    private function ensureSuperAdmin(Request $request): ?JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            return response()->json([
                'message' => 'Only the super admin can access this resource.',
            ], 403);
        }

        return null;
    }
}
