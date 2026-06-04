<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_access_seeder_creates_bootstrap_admin_from_config(): void
    {
        config()->set('admin_access.super_admin', [
            'name' => 'Root Admin',
            'email' => 'root@example.com',
            'phone' => '0999999999',
            'password' => 'secret123',
        ]);

        $this->seed(AdminAccessSeeder::class);

        $admin = User::query()->where('email', 'root@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertNull($admin->admin_role_id);
        $this->assertTrue((bool) $admin->is_active);
        $this->assertFalse((bool) $admin->is_deleted);
        $this->assertTrue(Hash::check('secret123', $admin->password_hash));
    }

    public function test_login_and_me_omit_admin_role_and_permissions(): void
    {
        $this->seed(AdminAccessSeeder::class);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@shop.local',
            'password' => 'password123',
        ]);

        $loginResponse->assertOk()
            ->assertJsonPath('user.role', User::ROLE_ADMIN)
            ->assertJsonMissingPath('user.admin_role')
            ->assertJsonMissingPath('user.permissions');

        $token = $loginResponse->json('access_token');

        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.role', User::ROLE_ADMIN)
            ->assertJsonMissingPath('user.admin_role')
            ->assertJsonMissingPath('user.permissions');
    }

    public function test_active_admin_can_crud_admin_accounts_without_admin_role_id(): void
    {
        $admin = $this->seededAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/admin/admins', [
            'full_name' => 'Child Admin',
            'email' => 'child-admin@example.com',
            'phone' => '0987654321',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.full_name', 'Child Admin')
            ->assertJsonPath('data.email', 'child-admin@example.com')
            ->assertJsonPath('data.role', User::ROLE_ADMIN)
            ->assertJsonMissingPath('data.admin_role');

        $childAdminId = $createResponse->json('data.id');

        $this->assertDatabaseHas('users', [
            'id' => $childAdminId,
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => null,
            'created_by_admin_id' => $admin->id,
        ]);

        $this->withToken($token)->getJson('/api/admin/admins?per_page=20')
            ->assertOk()
            ->assertJsonPath('per_page', 20)
            ->assertJsonPath('total', 2)
            ->assertJsonFragment(['email' => 'child-admin@example.com']);

        $this->withToken($token)->putJson("/api/admin/admins/{$childAdminId}", [
            'full_name' => 'Child Admin Updated',
            'email' => 'child-admin@example.com',
            'phone' => '0987654321',
            'is_active' => true,
        ])->assertOk()
            ->assertJsonPath('data.full_name', 'Child Admin Updated');

        $this->withToken($token)->patchJson("/api/admin/admins/{$childAdminId}/password", [
            'password' => 'newpassword123',
        ])->assertOk()
            ->assertJsonPath('message', 'Admin account password updated successfully.');

        $this->withToken($token)->patchJson("/api/admin/admins/{$childAdminId}/status", [
            'is_active' => false,
            'is_deleted' => true,
        ])->assertOk()
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.is_deleted', true);
    }

    public function test_non_admin_is_blocked_from_admin_management_routes(): void
    {
        $customer = User::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)->getJson('/api/admin/admins')
            ->assertStatus(403)
            ->assertJsonPath('message', 'You do not have permission to access this resource.');
    }

    public function test_inactive_admin_is_blocked_from_admin_management_routes(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => null,
            'is_active' => false,
            'is_deleted' => false,
        ]);
        $token = $admin->createToken('inactive-admin')->plainTextToken;

        $this->withToken($token)->getJson('/api/admin/admins')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Your account is not allowed to use this resource.');
    }

    public function test_last_active_admin_cannot_be_disabled(): void
    {
        $admin = $this->seededAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)->patchJson("/api/admin/admins/{$admin->id}/status", [
            'is_active' => false,
            'is_deleted' => true,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['admin']);
    }

    public function test_removed_access_endpoints_return_not_found(): void
    {
        $admin = $this->seededAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/admin/access/permissions')->assertNotFound();
        $this->withToken($token)->getJson('/api/admin/access/roles')->assertNotFound();
        $this->withToken($token)->getJson('/api/admin/access/admins')->assertNotFound();
    }

    private function seededAdmin(): User
    {
        $this->seed(AdminAccessSeeder::class);

        return User::query()
            ->where('email', config('admin_access.super_admin.email'))
            ->firstOrFail();
    }
}
