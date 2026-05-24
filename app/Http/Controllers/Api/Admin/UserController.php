<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use EnsuresAdminAccess;
    use PaginatesApiResults;

    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.users.view')) {
            return $response;
        }

        $users = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->withCount('orders')
            ->orderByDesc('id')
            ->paginate($this->perPage($request));

        return response()->json(
            $this->transformPaginator($users, fn (User $user): array => $this->customerPayload($user))
        );
    }

    public function show(Request $request, User $user): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.users.view')) {
            return $response;
        }

        if ($response = $this->ensureCustomerAccount($user)) {
            return $response;
        }

        return response()->json([
            'message' => 'Admin user retrieved successfully.',
            'data' => $this->customerPayload($user->loadCount('orders')),
        ]);
    }

    public function orders(Request $request, User $user): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.users.view')) {
            return $response;
        }

        if ($response = $this->ensureAdmin($request, 'admin.orders.view')) {
            return $response;
        }

        if ($response = $this->ensureCustomerAccount($user)) {
            return $response;
        }

        $orders = $this->orderService->listAdminOrders([
            'user_id' => $user->id,
            'status' => $request->query('status'),
            'keyword' => $request->query('keyword'),
        ], $this->perPage($request, 5));

        return response()->json(
            $this->transformPaginator(
                $orders,
                fn ($order): array => $this->orderService->orderSummaryPayload($order)
            )
        );
    }

    public function store(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.users.create')) {
            return $response;
        }

        $validated = $request->validate($this->rules());

        $user = User::query()->create([
            ...$this->profileAttributes($validated),
            'email' => Str::lower($validated['email']),
            'password_hash' => Hash::make($validated['password']),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => $validated['is_active'] ?? true,
            'is_deleted' => false,
        ]);

        return response()->json([
            'message' => 'Admin user created successfully.',
            'data' => $this->customerPayload($user->loadCount('orders')),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.users.update')) {
            return $response;
        }

        if ($response = $this->ensureCustomerAccount($user)) {
            return $response;
        }

        $validated = $request->validate($this->rules($user->id, true));
        $nextIsActive = $validated['is_active'] ?? $user->is_active;
        $nextIsDeleted = $validated['is_deleted'] ?? ($nextIsActive ? false : $user->is_deleted);

        $user->update([
            ...$this->profileAttributes($validated),
            'email' => Str::lower($validated['email']),
            'is_active' => $nextIsActive,
            'is_deleted' => $nextIsDeleted,
        ]);

        return response()->json([
            'message' => 'Admin user updated successfully.',
            'data' => $this->customerPayload($user->refresh()->loadCount('orders')),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.users.delete')) {
            return $response;
        }

        if ($response = $this->ensureCustomerAccount($user)) {
            return $response;
        }

        $user->markInactive();

        return response()->json([
            'message' => 'Admin user deactivated successfully.',
            'data' => $this->customerPayload($user->refresh()->loadCount('orders')),
        ]);
    }

    private function rules(?int $ignoreUserId = null, bool $updating = false): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($ignoreUserId),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($ignoreUserId),
            ],
            'password' => [$updating ? 'prohibited' : 'required', 'string', 'min:8'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'favorite_region' => ['nullable', 'string', 'max:120'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
            'newsletter' => ['nullable', 'boolean'],
            'sms_alerts' => ['nullable', 'boolean'],
            'order_email' => ['nullable', 'boolean'],
            'security_alerts' => ['nullable', 'boolean'],
            'reward_points' => ['nullable', 'integer', 'min:0'],
            'reward_tier' => ['nullable', 'string', 'max:50'],
            'next_tier_points' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
        ];
    }

    private function profileAttributes(array $validated): array
    {
        return [
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'favorite_region' => $validated['favorite_region'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'newsletter' => $validated['newsletter'] ?? false,
            'sms_alerts' => $validated['sms_alerts'] ?? false,
            'order_email' => $validated['order_email'] ?? true,
            'security_alerts' => $validated['security_alerts'] ?? true,
            'reward_points' => $validated['reward_points'] ?? 0,
            'reward_tier' => $validated['reward_tier'] ?? 'Bronze',
            'next_tier_points' => $validated['next_tier_points'] ?? 500,
        ];
    }

    private function ensureCustomerAccount(User $user): ?JsonResponse
    {
        if ($user->role === User::ROLE_CUSTOMER) {
            return null;
        }

        return response()->json([
            'message' => 'Only customer accounts can be managed from this endpoint.',
        ], 404);
    }

    private function customerPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'favorite_region' => $user->favorite_region,
            'avatar_url' => $user->avatar_url,
            'newsletter' => (bool) $user->newsletter,
            'sms_alerts' => (bool) $user->sms_alerts,
            'order_email' => (bool) $user->order_email,
            'security_alerts' => (bool) $user->security_alerts,
            'reward_points' => (int) $user->reward_points,
            'reward_tier' => $user->reward_tier,
            'next_tier_points' => (int) $user->next_tier_points,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
            'is_deleted' => (bool) $user->is_deleted,
            'orders_count' => (int) ($user->orders_count ?? $user->orders()->count()),
            'created_at' => optional($user->created_at)->toISOString(),
            'updated_at' => optional($user->updated_at)->toISOString(),
        ];
    }
}
