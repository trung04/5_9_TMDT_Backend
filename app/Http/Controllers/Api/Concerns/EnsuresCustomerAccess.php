<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait EnsuresCustomerAccess
{
    private function ensureCustomer(Request $request): ?JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->role !== User::ROLE_CUSTOMER) {
            return response()->json([
                'message' => 'You do not have permission to access this resource.',
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
