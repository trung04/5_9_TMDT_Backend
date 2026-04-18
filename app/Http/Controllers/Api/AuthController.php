<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new customer account and immediately issue a token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'full_name' => $request->string('full_name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString(),
            'password_hash' => Hash::make($request->string('password')->toString()),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        return $this->authenticatedResponse($user, 'User registered successfully.', 201);
    }

    /**
     * Sign a user in with email and password.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->string('email')->toString();
        $password = $request->string('password')->toString();
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Auth::guard('web')->validate([
            'email' => $email,
            'password' => $password,
        ])) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! $user->canAuthenticate()) {
            return response()->json([
                'message' => 'Your account is not allowed to sign in.',
            ], 403);
        }

        return $this->authenticatedResponse($user, 'Logged in successfully.');
    }

    /**
     * Return the authenticated user for the current token.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => $this->userData($user),
        ]);
    }

    /**
     * Revoke the access token used for the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->currentAccessToken()?->delete();
        Auth::forgetGuards();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Build the standard success response for register/login.
     */
    private function authenticatedResponse(User $user, string $message, int $status = 200): JsonResponse
    {
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => $message,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userData($user),
        ], $status);
    }

    /**
     * Format the public user payload returned by the auth API.
     *
     * @return array<string, mixed>
     */
    private function userData(User $user): array
    {
        return Arr::only($user->toArray(), [
            'id',
            'full_name',
            'email',
            'phone',
            'role',
            'status',
            'is_active',
            'created_at',
            'updated_at',
        ]);
    }
}
