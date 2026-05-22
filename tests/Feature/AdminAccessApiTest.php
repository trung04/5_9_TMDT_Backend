<?php

namespace Tests\Feature;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\User;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_access_seeder_creates_super_admin_from_config(): void
    {
        config()->set('admin_access.super_admin', [
            'name' => 'Root Admin',
            'email' => 'root@example.com',
            'phone' => '0999999999',
            'password' => 'secret123',
        ]);

        $this->seed(AdminAccessSeeder::class);

        $role = AdminRole::query()->where('slug', AdminRole::SUPER_ADMIN_SLUG)->firstOrFail();
        $admin = User::query()->where('email', 'root@example.com')->firstOrFail();

        $this->assertTrue($role->is_super);
        $this->assertTrue($role->is_system);
        $this->assertSame($role->id, $admin->admin_role_id);
        $this->assertTrue(Hash::check('secret123', $admin->password_hash));
        $this->assertSame(AdminPermission::query()->count(), $role->permissions()->count());
    }

    public function test_login_and_me_return_admin_role_and_permissions(): void
    {
        $this->seed(AdminAccessSeeder::class);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@shop.local',
            'password' => 'password123',
        ]);

        $loginResponse->assertOk()
            ->assertJsonPath('user.role', User::ROLE_ADMIN)
            ->assertJsonPath('user.admin_role.slug', AdminRole::SUPER_ADMIN_SLUG)
            ->assertJsonPath('user.admin_role.is_super', true);

        $this->assertContains('admin.dashboard.view', $loginResponse->json('user.permissions'));

        $token = $loginResponse->json('access_token');
        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.admin_role.slug', AdminRole::SUPER_ADMIN_SLUG);
    }

    public function test_super_admin_can_create_roles_and_admin_child_accounts(): void
    {
        $superAdmin = $this->seededSuperAdmin();
        $token = $superAdmin->createToken('test')->plainTextToken;

        $roleResponse = $this->withToken($token)->postJson('/api/admin/access/roles', [
            'name' => 'Catalog Operator',
            'description' => 'Can read catalog data.',
            'permissions' => ['admin.products.view'],
        ]);

        $roleResponse->assertCreated()
            ->assertJsonPath('data.name', 'Catalog Operator')
            ->assertJsonPath('data.permission_keys.0', 'admin.products.view');

        $adminResponse = $this->withToken($token)->postJson('/api/admin/access/admins', [
            'full_name' => 'Child Admin',
            'email' => 'child-admin@example.com',
            'phone' => '0987654321',
            'password' => 'password123',
            'admin_role_id' => $roleResponse->json('data.id'),
        ]);

        $adminResponse->assertCreated()
            ->assertJsonPath('data.email', 'child-admin@example.com')
            ->assertJsonPath('data.admin_role.name', 'Catalog Operator');
    }

    public function test_admin_child_is_limited_by_role_permissions(): void
    {
        $this->seed(AdminAccessSeeder::class);
        $permission = AdminPermission::query()->where('key', 'admin.products.view')->firstOrFail();
        $role = AdminRole::query()->create([
            'name' => 'Product Viewer',
            'slug' => 'product_viewer',
            'description' => null,
        ]);
        $role->permissions()->sync([$permission->id]);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => $role->id,
        ]);
        $token = $admin->createToken('child')->plainTextToken;

        $this->withToken($token)->getJson('/api/admin/products')
            ->assertOk();

        $this->withToken($token)->deleteJson('/api/admin/products/1')
            ->assertStatus(403)
            ->assertJsonPath('message', 'You do not have permission to access this resource.');
    }

    public function test_non_admin_is_blocked_from_admin_supplier_routes(): void
    {
        $customer = User::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)->postJson('/api/admin/suppliers', [
            'supplier_code' => 'SUP-TEST',
            'name' => 'Supplier Test',
            'phone' => '0909090909',
        ])->assertStatus(403)
            ->assertJsonPath('message', 'You do not have permission to access this resource.');
    }

    public function test_super_admin_role_and_last_super_admin_are_protected(): void
    {
        $superAdmin = $this->seededSuperAdmin();
        $token = $superAdmin->createToken('test')->plainTextToken;
        $superRole = AdminRole::query()->where('slug', AdminRole::SUPER_ADMIN_SLUG)->firstOrFail();

        $this->withToken($token)->deleteJson("/api/admin/access/roles/{$superRole->id}")
            ->assertStatus(422);

        $this->withToken($token)->patchJson("/api/admin/access/admins/{$superAdmin->id}/status", [
            'is_active' => false,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['admin']);
    }

    private function seededSuperAdmin(): User
    {
        $this->seed(AdminAccessSeeder::class);

        return User::query()
            ->where('email', config('admin_access.super_admin.email'))
            ->firstOrFail()
            ->load(['adminRole.permissions']);
    }
}
