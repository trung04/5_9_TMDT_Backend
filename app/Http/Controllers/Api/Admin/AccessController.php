<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Controller;
use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccessController extends Controller
{
    use EnsuresAdminAccess;

    public function permissions(Request $request): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        return response()->json([
            'message' => 'Admin permissions retrieved successfully.',
            'data' => AdminPermission::query()
                ->orderBy('group')
                ->orderBy('key')
                ->get()
                ->map(fn (AdminPermission $permission): array => $this->permissionPayload($permission))
                ->values()
                ->all(),
        ]);
    }

    public function roles(Request $request): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        $roles = AdminRole::query()
            ->with(['permissions'])
            ->withCount('users')
            ->orderByDesc('is_super')
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Admin roles retrieved successfully.',
            'data' => $roles->map(fn (AdminRole $role): array => $this->rolePayload($role))->values()->all(),
        ]);
    }

    public function storeRole(Request $request): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        $validated = $request->validate($this->roleRules());

        /** @var User $user */
        $user = $request->user();

        $role = DB::transaction(function () use ($validated, $user): AdminRole {
            $role = AdminRole::query()->create([
                'name' => $validated['name'],
                'slug' => $this->uniqueRoleSlug($validated['name']),
                'description' => $validated['description'] ?? null,
                'is_super' => false,
                'is_system' => false,
                'created_by_admin_id' => $user->id,
            ]);

            $this->syncRolePermissions($role, $validated['permissions'] ?? []);

            return $role->refresh()->load('permissions');
        });

        return response()->json([
            'message' => 'Admin role created successfully.',
            'data' => $this->rolePayload($role),
        ], 201);
    }

    public function updateRole(Request $request, int $role): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        $roleModel = AdminRole::query()->with('permissions')->find($role);

        if (! $roleModel) {
            return response()->json(['message' => 'Admin role not found.'], 404);
        }

        if ($roleModel->is_super) {
            return response()->json(['message' => 'The super admin role cannot be modified.'], 422);
        }

        $validated = $request->validate($this->roleRules($roleModel->id));

        $roleModel = DB::transaction(function () use ($validated, $roleModel): AdminRole {
            $roleModel->update([
                'name' => $validated['name'],
                'slug' => $this->uniqueRoleSlug($validated['name'], $roleModel->id),
                'description' => $validated['description'] ?? null,
            ]);

            $this->syncRolePermissions($roleModel, $validated['permissions'] ?? []);

            return $roleModel->refresh()->load('permissions');
        });

        return response()->json([
            'message' => 'Admin role updated successfully.',
            'data' => $this->rolePayload($roleModel),
        ]);
    }

    public function destroyRole(Request $request, int $role): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        $roleModel = AdminRole::query()->withCount('users')->find($role);

        if (! $roleModel) {
            return response()->json(['message' => 'Admin role not found.'], 404);
        }

        if ($roleModel->is_super || $roleModel->is_system) {
            return response()->json(['message' => 'System admin roles cannot be deleted.'], 422);
        }

        if ($roleModel->users_count > 0) {
            return response()->json(['message' => 'This role is assigned to admin accounts.'], 422);
        }

        $roleModel->delete();

        return response()->json(['message' => 'Admin role deleted successfully.']);
    }

    public function admins(Request $request): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        $admins = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->with(['adminRole', 'createdByAdmin'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Admin accounts retrieved successfully.',
            'data' => $admins->map(fn (User $admin): array => $this->adminPayload($admin))->values()->all(),
        ]);
    }

    public function storeAdmin(Request $request): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        $validated = $request->validate($this->adminRules());
        $role = $this->assignableAdminRole((int) $validated['admin_role_id']);

        /** @var User $creator */
        $creator = $request->user();

        $admin = User::query()->create([
            'full_name' => $validated['full_name'],
            'email' => Str::lower($validated['email']),
            'phone' => $validated['phone'],
            'password_hash' => Hash::make($validated['password']),
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => $role->id,
            'created_by_admin_id' => $creator->id,
            'is_active' => $validated['is_active'] ?? true,
            'is_deleted' => false,
        ]);

        return response()->json([
            'message' => 'Admin account created successfully.',
            'data' => $this->adminPayload($admin->load(['adminRole', 'createdByAdmin'])),
        ], 201);
    }

    public function updateAdmin(Request $request, int $admin): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        $adminModel = $this->findAdmin($admin);

        if (! $adminModel) {
            return response()->json(['message' => 'Admin account not found.'], 404);
        }

        $validated = $request->validate($this->adminRules($adminModel->id, true));
        $role = $this->assignableAdminRole((int) $validated['admin_role_id'], $adminModel->isSuperAdmin());
        $nextIsActive = $validated['is_active'] ?? $adminModel->is_active;
        $nextIsDeleted = $validated['is_deleted'] ?? ($nextIsActive ? false : $adminModel->is_deleted);
        $this->assertNotRemovingLastSuperAdmin($adminModel, [
            'admin_role_id' => $role->id,
            'is_active' => $nextIsActive,
            'is_deleted' => $nextIsDeleted,
        ]);

        $adminModel->update([
            'full_name' => $validated['full_name'],
            'email' => Str::lower($validated['email']),
            'phone' => $validated['phone'],
            'admin_role_id' => $role->id,
            'is_active' => $nextIsActive,
            'is_deleted' => $nextIsDeleted,
        ]);

        return response()->json([
            'message' => 'Admin account updated successfully.',
            'data' => $this->adminPayload($adminModel->refresh()->load(['adminRole', 'createdByAdmin'])),
        ]);
    }

    public function updateAdminStatus(Request $request, int $admin): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        $adminModel = $this->findAdmin($admin);

        if (! $adminModel) {
            return response()->json(['message' => 'Admin account not found.'], 404);
        }

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
        ]);
        $nextIsDeleted = $validated['is_deleted'] ?? ($validated['is_active'] ? false : $adminModel->is_deleted);

        $this->assertNotRemovingLastSuperAdmin($adminModel, [
            'is_active' => $validated['is_active'],
            'is_deleted' => $nextIsDeleted,
        ]);

        $adminModel->update([
            'is_active' => $validated['is_active'],
            'is_deleted' => $nextIsDeleted,
        ]);

        return response()->json([
            'message' => 'Admin account status updated successfully.',
            'data' => $this->adminPayload($adminModel->refresh()->load(['adminRole', 'createdByAdmin'])),
        ]);
    }

    public function updateAdminPassword(Request $request, int $admin): JsonResponse
    {
        if ($response = $this->ensureSuperAdmin($request)) {
            return $response;
        }

        $adminModel = $this->findAdmin($admin);

        if (! $adminModel) {
            return response()->json(['message' => 'Admin account not found.'], 404);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $adminModel->update([
            'password_hash' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Admin account password updated successfully.']);
    }

    private function roleRules(?int $ignoreRoleId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', 'distinct', 'exists:admin_permissions,key'],
        ];
    }

    private function adminRules(?int $ignoreUserId = null, bool $updating = false): array
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
            'admin_role_id' => ['required', 'integer', 'exists:admin_roles,id'],
            'is_active' => ['nullable', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
            'password' => [$updating ? 'sometimes' : 'required', 'string', 'min:8'],
        ];
    }

    private function syncRolePermissions(AdminRole $role, array $permissionKeys): void
    {
        $permissionIds = AdminPermission::query()
            ->whereIn('key', $permissionKeys)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);
    }

    private function uniqueRoleSlug(string $name, ?int $ignoreRoleId = null): string
    {
        $base = Str::slug($name, '_') ?: 'admin_role';
        $slug = $base;
        $counter = 2;

        while (
            AdminRole::query()
                ->where('slug', $slug)
                ->when($ignoreRoleId, fn ($query) => $query->whereKeyNot($ignoreRoleId))
                ->exists()
        ) {
            $slug = "{$base}_{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function assignableAdminRole(int $roleId, bool $allowSuper = false): AdminRole
    {
        $role = AdminRole::query()->find($roleId);

        if (! $role || ($role->is_super && ! $allowSuper)) {
            throw ValidationException::withMessages([
                'admin_role_id' => ['The selected admin role cannot be assigned.'],
            ]);
        }

        return $role;
    }

    private function findAdmin(int $id): ?User
    {
        return User::query()
            ->whereKey($id)
            ->where('role', User::ROLE_ADMIN)
            ->with(['adminRole.permissions', 'createdByAdmin'])
            ->first();
    }

    private function assertNotRemovingLastSuperAdmin(User $admin, array $nextState): void
    {
        if (! $admin->isSuperAdmin()) {
            return;
        }

        $nextRoleId = (int) ($nextState['admin_role_id'] ?? $admin->admin_role_id);
        $nextRole = AdminRole::query()->find($nextRoleId);
        $willRemainSuper = $nextRole?->is_super
            && (bool) ($nextState['is_active'] ?? $admin->is_active)
            && ! (bool) ($nextState['is_deleted'] ?? $admin->is_deleted);

        if ($willRemainSuper || $this->activeSuperAdminCount() > 1) {
            return;
        }

        throw ValidationException::withMessages([
            'admin' => ['The last active super admin cannot be disabled or demoted.'],
        ]);
    }

    private function activeSuperAdminCount(): int
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->available()
            ->whereHas('adminRole', fn ($query) => $query->where('is_super', true))
            ->count();
    }

    private function permissionPayload(AdminPermission $permission): array
    {
        return [
            'id' => $permission->id,
            'key' => $permission->key,
            'name' => $permission->name,
            'group' => $permission->group,
            'description' => $permission->description,
        ];
    }

    private function rolePayload(AdminRole $role): array
    {
        $role->loadMissing('permissions');

        return [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'is_super' => (bool) $role->is_super,
            'is_system' => (bool) $role->is_system,
            'users_count' => (int) ($role->users_count ?? $role->users()->count()),
            'permissions' => $role->permissions
                ->map(fn (AdminPermission $permission): array => $this->permissionPayload($permission))
                ->values()
                ->all(),
            'permission_keys' => $role->permissions->pluck('key')->values()->all(),
            'created_at' => optional($role->created_at)->toISOString(),
            'updated_at' => optional($role->updated_at)->toISOString(),
        ];
    }

    private function adminPayload(User $admin): array
    {
        $admin->loadMissing(['adminRole', 'createdByAdmin']);

        return [
            'id' => $admin->id,
            'full_name' => $admin->full_name,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'role' => $admin->role,
            'is_active' => (bool) $admin->is_active,
            'is_deleted' => (bool) $admin->is_deleted,
            'admin_role' => $admin->adminRole ? [
                'id' => $admin->adminRole->id,
                'name' => $admin->adminRole->name,
                'slug' => $admin->adminRole->slug,
                'is_super' => (bool) $admin->adminRole->is_super,
            ] : null,
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
