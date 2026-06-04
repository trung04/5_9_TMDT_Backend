<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_without_admin_role_can_crud_customer_accounts(): void
    {
        $admin = $this->plainAdmin();
        $token = $admin->createToken('admin')->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/admin/users', [
            'full_name' => 'Customer Managed',
            'email' => 'managed-customer@example.com',
            'phone' => '0901111222',
            'password' => 'password123',
            'address' => '12 Nguyen Trai',
            'city' => 'Ha Noi',
            'favorite_region' => 'Dong Bac',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.email', 'managed-customer@example.com')
            ->assertJsonPath('data.role', User::ROLE_CUSTOMER)
            ->assertJsonPath('data.orders_count', 0);
        $this->assertCustomerPayloadOmitsRemovedFields($createResponse);

        $customerId = $createResponse->json('data.id');

        $listResponse = $this->withToken($token)->getJson('/api/admin/users');

        $listResponse
            ->assertOk()
            ->assertJsonPath('per_page', 15)
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['email' => 'managed-customer@example.com']);
        $this->assertCustomerPayloadOmitsRemovedFields($listResponse, 'data.0');

        $updateResponse = $this->withToken($token)->putJson("/api/admin/users/{$customerId}", [
            'full_name' => 'Customer Updated',
            'email' => 'managed-customer@example.com',
            'phone' => '0901111222',
            'address' => '88 Tran Hung Dao',
            'city' => 'Da Nang',
            'favorite_region' => 'Duyen hai mien Trung',
            'is_active' => true,
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.full_name', 'Customer Updated')
            ->assertJsonPath('data.is_active', true);
        $this->assertCustomerPayloadOmitsRemovedFields($updateResponse);

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

    public function test_non_admin_cannot_manage_customer_accounts(): void
    {
        $customer = User::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)->getJson('/api/admin/users')
            ->assertStatus(403)
            ->assertJsonPath('message', 'You do not have permission to access this resource.');
    }

    public function test_admin_can_view_customer_order_history_from_user_endpoint(): void
    {
        $admin = $this->plainAdmin();
        $token = $admin->createToken('admin')->plainTextToken;
        $customer = User::factory()->create([
            'full_name' => 'Customer History',
        ]);
        $otherCustomer = User::factory()->create([
            'full_name' => 'Other Customer',
        ]);

        $firstOrder = Order::query()->create([
            'user_id' => $customer->id,
            'order_no' => 'ORD-CUSTOMER-001',
            'recipient_name' => $customer->full_name,
            'recipient_phone' => $customer->phone,
            'shipping_address' => '12 Nguyen Trai',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => Order::STATUS_PENDING,
            'subtotal' => 100000,
            'shipping_fee' => 20000,
            'discount_amount' => 0,
            'total_amount' => 120000,
            'stock_deducted' => false,
        ]);

        $secondOrder = Order::query()->create([
            'user_id' => $customer->id,
            'order_no' => 'ORD-CUSTOMER-002',
            'recipient_name' => $customer->full_name,
            'recipient_phone' => $customer->phone,
            'shipping_address' => '34 Tran Phu',
            'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
            'status' => Order::STATUS_CONFIRMED,
            'subtotal' => 250000,
            'shipping_fee' => 0,
            'discount_amount' => 10000,
            'total_amount' => 240000,
            'stock_deducted' => false,
        ]);

        Order::query()->create([
            'user_id' => $otherCustomer->id,
            'order_no' => 'ORD-OTHER-001',
            'recipient_name' => $otherCustomer->full_name,
            'recipient_phone' => $otherCustomer->phone,
            'shipping_address' => '99 Le Loi',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => Order::STATUS_PENDING,
            'subtotal' => 90000,
            'shipping_fee' => 15000,
            'discount_amount' => 0,
            'total_amount' => 105000,
            'stock_deducted' => false,
        ]);

        $this->withToken($token)->getJson("/api/admin/users/{$customer->id}/orders?per_page=1")
            ->assertOk()
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.customer.id', $customer->id)
            ->assertJsonPath('data.0.order_no', $secondOrder->order_no);

        $this->withToken($token)->getJson('/api/admin/orders?per_page=100')
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonFragment(['order_no' => $firstOrder->order_no])
            ->assertJsonFragment(['order_no' => $secondOrder->order_no]);
    }

    public function test_customer_order_history_requires_admin_role(): void
    {
        $customer = User::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)->getJson("/api/admin/users/{$customer->id}/orders")
            ->assertStatus(403)
            ->assertJsonPath('message', 'You do not have permission to access this resource.');
    }

    public function test_soft_delete_preserves_customer_orders(): void
    {
        $admin = $this->plainAdmin();
        $token = $admin->createToken('admin')->plainTextToken;
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
        $admin = $this->plainAdmin();
        $token = $admin->createToken('admin')->plainTextToken;

        $this->withToken($token)->deleteJson("/api/admin/users/{$admin->id}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Only customer accounts can be managed from this endpoint.');
    }

    public function test_customer_order_history_endpoint_rejects_non_customer_accounts(): void
    {
        $admin = $this->plainAdmin();
        $token = $admin->createToken('admin')->plainTextToken;

        $this->withToken($token)->getJson("/api/admin/users/{$admin->id}/orders")
            ->assertNotFound()
            ->assertJsonPath('message', 'Only customer accounts can be managed from this endpoint.');
    }

    private function plainAdmin(): User
    {
        $this->seed(AdminAccessSeeder::class);

        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => null,
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function assertCustomerPayloadOmitsRemovedFields(\Illuminate\Testing\TestResponse $response, string $path = 'data'): void
    {
        foreach ([
            'newsletter',
            'sms_alerts',
            'order_email',
            'security_alerts',
            'reward_points',
            'reward_tier',
            'next_tier_points',
        ] as $field) {
            $response->assertJsonMissingPath("{$path}.{$field}");
        }
    }
}
