<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminAccountController extends Controller
{
    use EnsuresAdminAccess;
    use PaginatesApiResults;

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $admins = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->with('createdByAdmin')
            ->orderByDesc('id')
            ->paginate($this->perPage($request));

        return response()->json(
            $this->transformPaginator($admins, fn (User $admin): array => $this->adminPayload($admin))
        );
    }

    public function store(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validated = $request->validate($this->rules());

        /** @var User $creator */
        $creator = $request->user();

        $admin = User::query()->create([
            'full_name' => $validated['full_name'],
            'email' => Str::lower($validated['email']),
            'phone' => $validated['phone'],
            'password_hash' => Hash::make($validated['password']),
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => null,
            'created_by_admin_id' => $creator->id,
            'is_active' => $validated['is_active'] ?? true,
            'is_deleted' => $validated['is_deleted'] ?? false,
        ]);

        return response()->json([
            'message' => 'Admin account created successfully.',
            'data' => $this->adminPayload($admin->load('createdByAdmin')),
        ], 201);
    }

    public function update(Request $request, User $admin): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $admin = $this->findAdmin($admin->id);
        $validated = $request->validate($this->rules($admin->id, true));
        $nextIsActive = $validated['is_active'] ?? $admin->is_active;
        $nextIsDeleted = $validated['is_deleted'] ?? ($nextIsActive ? false : $admin->is_deleted);

        $this->assertNotRemovingLastActiveAdmin($admin, [
            'is_active' => $nextIsActive,
            'is_deleted' => $nextIsDeleted,
        ]);

        $admin->update([
            'full_name' => $validated['full_name'],
            'email' => Str::lower($validated['email']),
            'phone' => $validated['phone'],
            'is_active' => $nextIsActive,
            'is_deleted' => $nextIsDeleted,
        ]);

        return response()->json([
            'message' => 'Admin account updated successfully.',
            'data' => $this->adminPayload($admin->refresh()->load('createdByAdmin')),
        ]);
    }

    public function updateStatus(Request $request, User $admin): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $admin = $this->findAdmin($admin->id);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
        ]);
        $nextIsDeleted = $validated['is_deleted'] ?? ($validated['is_active'] ? false : $admin->is_deleted);

        $this->assertNotRemovingLastActiveAdmin($admin, [
            'is_active' => $validated['is_active'],
            'is_deleted' => $nextIsDeleted,
        ]);

        $admin->update([
            'is_active' => $validated['is_active'],
            'is_deleted' => $nextIsDeleted,
        ]);

        return response()->json([
            'message' => 'Admin account status updated successfully.',
            'data' => $this->adminPayload($admin->refresh()->load('createdByAdmin')),
        ]);
    }

    public function updatePassword(Request $request, User $admin): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $admin = $this->findAdmin($admin->id);
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $admin->update([
            'password_hash' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Admin account password updated successfully.',
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
            'password' => [$updating ? 'sometimes' : 'required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
        ];
    }

    private function findAdmin(int $id): User
    {
        $admin = User::query()
            ->whereKey($id)
            ->where('role', User::ROLE_ADMIN)
            ->with('createdByAdmin')
            ->first();

        abort_if(! $admin, 404, 'Admin account not found.');

        return $admin;
    }

    /**
     * @param  array<string, mixed>  $nextState
     */
    private function assertNotRemovingLastActiveAdmin(User $admin, array $nextState): void
    {
        $willRemainActive = (bool) ($nextState['is_active'] ?? $admin->is_active)
            && ! (bool) ($nextState['is_deleted'] ?? $admin->is_deleted);

        if ($willRemainActive || $this->activeAdminCount() > 1) {
            return;
        }

        throw ValidationException::withMessages([
            'admin' => ['The last active admin account cannot be disabled.'],
        ]);
    }

    private function activeAdminCount(): int
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->available()
            ->count();
    }

    private function adminPayload(User $admin): array
    {
        return [
            'id' => $admin->id,
            'full_name' => $admin->full_name,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'role' => $admin->role,
            'is_active' => (bool) $admin->is_active,
            'is_deleted' => (bool) $admin->is_deleted,
            'created_by_admin' => $admin->createdByAdmin ? [
                'id' => $admin->createdByAdmin->id,
                'full_name' => $admin->createdByAdmin->full_name,
                'email' => $admin->createdByAdmin->email,
            ] : null,
            'created_at' => optional($admin->created_at)->toISOString(),
            'updated_at' => optional($admin->updated_at)->toISOString(),
        ];
    }
}
