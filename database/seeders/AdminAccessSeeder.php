<?php

namespace Database\Seeders;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminAccessSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $permissions = $this->seedPermissions();
            $superRole = $this->seedSuperRole($permissions);
            $this->seedSuperAdmin($superRole);
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, AdminPermission>
     */
    private function seedPermissions()
    {
        $configuredPermissions = collect(config('admin_access.permissions', []));

        $configuredPermissions->each(function (array $permission): void {
            AdminPermission::query()->updateOrCreate(
                ['key' => $permission['key']],
                [
                    'name' => $permission['name'],
                    'group' => $permission['group'],
                    'description' => $permission['description'] ?? null,
                ],
            );
        });

        return AdminPermission::query()
            ->whereIn('key', $configuredPermissions->pluck('key')->all())
            ->orderBy('group')
            ->orderBy('key')
            ->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, AdminPermission>  $permissions
     */
    private function seedSuperRole($permissions): AdminRole
    {
        $role = AdminRole::query()->updateOrCreate(
            ['slug' => AdminRole::SUPER_ADMIN_SLUG],
            [
                'name' => 'Super Admin',
                'description' => 'System role with unrestricted admin access.',
                'is_super' => true,
                'is_system' => true,
                'created_by_admin_id' => null,
            ],
        );

        $role->permissions()->sync($permissions->pluck('id')->all());

        return $role->refresh();
    }

    private function seedSuperAdmin(AdminRole $superRole): User
    {
        $config = config('admin_access.super_admin');
        $email = Str::lower(trim((string) ($config['email'] ?? 'admin@shop.local')));
        $phone = trim((string) ($config['phone'] ?? '0900000001'));

        $admin = User::query()
            ->where('email', $email)
            ->orWhere('phone', $phone)
            ->first() ?? new User();

        $admin->forceFill([
            'full_name' => trim((string) ($config['name'] ?? 'Super Admin')) ?: 'Super Admin',
            'email' => $email,
            'phone' => $phone,
            'password_hash' => Hash::make((string) ($config['password'] ?? 'password123')),
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => $superRole->id,
            'created_by_admin_id' => null,
            'is_active' => true,
            'is_deleted' => false,
        ])->save();

        return $admin->refresh();
    }
}
