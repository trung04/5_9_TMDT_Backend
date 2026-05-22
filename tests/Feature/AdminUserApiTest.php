<?php

namespace Tests\Feature;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_crud_customer_accounts(): void
    {
        $superAdmin = $this->seededSuperAdmin();
        $token = $superAdmin->createToken('super-admin')->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/admin/users', [
            'full_name' => 'Customer Managed',
            'email' => 'managed-customer@example.com',
            'phone' => '0901111222',
            'password' => 'password123',
            'address' => '12 Nguyen Trai',
            'city' => 'Ha Noi',
            'favorite_region' => 'Dong Bac',
            'newsletter' => true,
            'sms_alerts' => false,
            'order_email' => true,
            'security_alerts' => true,
            'reward_points' => 120,
            'reward_tier' => 'Silver',
            'next_tier_points' => 1000,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.email', 'managed-customer@example.com')
            ->assertJsonPath('data.role', User::ROLE_CUSTOMER)
            ->assertJsonPath('data.orders_count', 0);

        $customerId = $createResponse->json('data.id');

        $this->withToken($token)->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonFragment(['email' => 'managed-customer@example.com']);

        $this->withToken($token)->putJson("/api/admin/users/{$customerId}", [
            'full_name' => 'Customer Updated',
            'email' => 'managed-customer@example.com',
            'phone' => '0901111222',
            'address' => '88 Tran Hung Dao',
            'city' => 'Da Nang',
            'favorite_region' => 'Duyen hai mien Trung',
            'newsletter' => false,
            'sms_alerts' => true,
            'order_email' => true,
            'security_alerts' => true,
            'reward_points' => 250,
            'reward_tier' => 'Gold',
            'next_tier_points' => 1500,
            'is_active' => true,
        ])->assertOk()
            ->assertJsonPath('data.full_name', 'Customer Updated')
            ->assertJsonPath('data.reward_tier', 'Gold');

        $this->withToken($token)->deleteJson("/api/admin/users/{$customerId}")
            ->assertOk()
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.is_deleted', false);

        $this->assertDatabaseHas('users', [
            'id' => $customerId,
            'is_active' => false,
            'is_deleted' => false,
        ]);
    }

    public function test_child_admin_is_limited_by_user_permissions(): void
    {
        $this->seed(AdminAccessSeeder::class);
        $admin = $this->childAdminWithPermissions(['admin.users.view']);
        $token = $admin->createToken('user-viewer')->plainTextToken;

        $this->withToken($token)->getJson('/api/admin/users')
            ->assertOk();

        $this->withToken($token)->postJson('/api/admin/users', [
            'full_name' => 'Denied Customer',
            'email' => 'denied-customer@example.com',
            'phone' => '0902222333',
            'password' => 'password123',
        ])->assertStatus(403)
            ->assertJsonPath('message', 'You do not have permission to access this resource.');
    }

    public function test_soft_delete_preserves_customer_orders(): void
    {
        $superAdmin = $this->seededSuperAdmin();
        $token = $superAdmin->createToken('super-admin')->plainTextToken;
        $customer = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_no' => 'ORD-TEST-USER',
            'recipient_name' => $customer->full_name,
            'recipient_phone' => $customer->phone,
            'shipping_address' => '101 Test Street',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => Order::STATUS_PENDING,
            'subtotal' => 100000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'stock_deducted' => false,
        ]);

        $this->withToken($token)->deleteJson("/api/admin/users/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.orders_count', 1);

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'is_active' => false,
            'is_deleted' => false,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $customer->id,
        ]);
    }

    public function test_admin_user_endpoint_rejects_non_customer_accounts(): void
    {
        $superAdmin = $this->seededSuperAdmin();
        $token = $superAdmin->createToken('super-admin')->plainTextToken;

        $this->withToken($token)->deleteJson("/api/admin/users/{$superAdmin->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Only customer accounts can be managed from this endpoint.');
    }

    private function seededSuperAdmin(): User
    {
        $this->seed(AdminAccessSeeder::class);

        return User::query()
            ->where('email', config('admin_access.super_admin.email'))
            ->firstOrFail()
            ->load(['adminRole.permissions']);
    }

    /**
     * @param list<string> $permissionKeys
     */
    private function childAdminWithPermissions(array $permissionKeys): User
    {
        $permissions = AdminPermission::query()
            ->whereIn('key', $permissionKeys)
            ->pluck('id')
            ->all();

        $role = AdminRole::query()->create([
            'name' => 'User Operator',
            'slug' => 'user_operator',
            'description' => null,
        ]);
        $role->permissions()->sync($permissions);

        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => $role->id,
        ])->load(['adminRole.permissions']);
    }
}
