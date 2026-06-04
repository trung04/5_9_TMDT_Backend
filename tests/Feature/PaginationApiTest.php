<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Region;
use App\Models\Supplier;
use App\Models\SupportTicket;
use App\Models\User;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaginationApiTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence = 1;

    public function test_public_catalog_and_posts_return_laravel_paginators_with_filters_and_clamps(): void
    {
        $category = $this->createCategory('Mango Category');
        $otherCategory = $this->createCategory('Other Category');
        $supplier = $this->createSupplier('SUP-MANGO', 'Mango Supplier');
        $region = $this->createRegion('mekong', 'Mekong');
        $otherRegion = $this->createRegion('north', 'North');

        $matchingA = $this->createProduct([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'region_id' => $region->id,
            'sku' => 'MANGO-A',
            'name' => 'Mango Alpha',
        ]);
        $this->createProduct([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'region_id' => $region->id,
            'sku' => 'MANGO-B',
            'name' => 'Mango Beta',
        ]);
        $this->createProduct([
            'category_id' => $otherCategory->id,
            'supplier_id' => $supplier->id,
            'region_id' => $otherRegion->id,
            'sku' => 'TEA-A',
            'name' => 'Green Tea',
        ]);

        $this->getJson("/api/products?category_id={$category->id}&region_id={$region->id}&keyword=Mango&per_page=999")
            ->assertOk()
            ->assertJsonPath('per_page', 100)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(2, 'data');

        $lowClamp = $this->getJson('/api/products?per_page=0');
        $lowClamp->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonCount(1, 'data');
        $this->assertArrayNotHasKey('message', $lowClamp->json());

        $this->getJson("/api/categories/{$category->id}/products?search=Mango&per_page=1")
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.id', $matchingA->id);

        $this->getJson("/api/suppliers/{$supplier->id}/products?per_page=1")
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 3);

        $author = User::factory()->create();
        $this->createPost($author, 'Public pagination post A');
        $this->createPost($author, 'Public pagination post B');
        $this->createPost($author, 'Draft pagination post', Post::STATUS_DRAFT);

        $this->getJson('/api/posts?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_list_endpoints_return_laravel_paginators(): void
    {
        $token = $this->superAdminToken();
        $admin = $this->seededSuperAdmin();
        $category = $this->createCategory('Admin Product Category');
        $supplier = $this->createSupplier('SUP-ADMIN', 'Admin Product Supplier');

        $this->createProduct([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'ADMIN-PROD-A',
            'name' => 'Admin Product Alpha',
        ]);
        $this->createProduct([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'ADMIN-PROD-B',
            'name' => 'Admin Product Beta',
        ]);

        $this->withToken($token)->getJson('/api/admin/products?keyword=Admin Product&per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');

        $this->createPost($admin, 'Admin pagination post A');
        $this->createPost($admin, 'Admin pagination post B');

        $this->withToken($token)->getJson('/api/admin/posts?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');

        User::factory()->create(['email' => 'customer-page-a@example.com']);
        User::factory()->create(['email' => 'customer-page-b@example.com']);

        $this->withToken($token)->getJson('/api/admin/users?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');

        $this->withToken($token)->getJson('/api/admin/admins?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_order_filters_work_with_pagination(): void
    {
        $token = $this->superAdminToken();
        $customer = User::factory()->create(['full_name' => 'Needle Customer']);

        $matching = $this->createOrder($customer, 'ORD-NEEDLE-1', Order::STATUS_PENDING);
        $this->createOrder($customer, 'ORD-NEEDLE-2', Order::STATUS_DELIVERED);
        $this->createOrder(User::factory()->create(['full_name' => 'Other Customer']), 'ORD-OTHER-1', Order::STATUS_PENDING);

        $this->withToken($token)->getJson('/api/admin/orders?status=PENDING&keyword=Needle&per_page=10')
            ->assertOk()
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $matching->id);
    }

    public function test_account_notifications_complaints_and_support_tickets_are_paginated(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('customer')->plainTextToken;
        $otherUser = User::factory()->create();
        $product = $this->createProduct();
        $order = $this->createOrder($user, 'ORD-COMPLAINT-1');

        Notification::query()->create([
            'user_id' => $user->id,
            'title' => 'Notice A',
            'message' => 'Message A',
            'channel' => Notification::CHANNEL_SYSTEM,
            'status' => Notification::STATUS_SENT,
            'sent_at' => now(),
        ]);
        Notification::query()->create([
            'user_id' => $user->id,
            'title' => 'Notice B',
            'message' => 'Message B',
            'channel' => Notification::CHANNEL_SYSTEM,
            'status' => Notification::STATUS_SENT,
            'sent_at' => now(),
        ]);
        Notification::query()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other Notice',
            'message' => 'Other Message',
            'channel' => Notification::CHANNEL_SYSTEM,
            'status' => Notification::STATUS_SENT,
        ]);

        $this->withToken($token)->getJson('/api/notifications?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');

        Complaint::query()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'reason' => 'Damaged package',
            'content' => 'Package was damaged.',
            'status' => Complaint::STATUS_OPEN,
        ]);
        Complaint::query()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'reason' => 'Missing item',
            'content' => 'One item was missing.',
            'status' => Complaint::STATUS_OPEN,
        ]);

        $this->withToken($token)->getJson('/api/complaints?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');

        SupportTicket::query()->create([
            'user_id' => $user->id,
            'subject' => 'Supplier support A',
            'message' => 'Support A',
            'channel' => 'SUPPLIER',
            'status' => SupportTicket::STATUS_OPEN,
        ]);
        SupportTicket::query()->create([
            'user_id' => $user->id,
            'subject' => 'Supplier support B',
            'message' => 'Support B',
            'channel' => 'SUPPLIER',
            'status' => SupportTicket::STATUS_OPEN,
        ]);
        SupportTicket::query()->create([
            'user_id' => $user->id,
            'subject' => 'Warehouse support',
            'message' => 'Support warehouse',
            'channel' => 'WAREHOUSE',
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        $this->withToken($token)->getJson('/api/support-tickets?channel=supplier&per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');
    }

    public function test_operations_lists_are_paginated(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('operations')->plainTextToken;
        $productA = $this->createProduct(['sku' => 'OPS-A', 'name' => 'Operations Product A']);
        $productB = $this->createProduct(['sku' => 'OPS-B', 'name' => 'Operations Product B']);
        $inventoryId = DB::table('inventories')->insertGetId([
            'name' => 'Main warehouse',
            'location' => 'Ha Noi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([$productA, $productB] as $product) {
            DB::table('inventory_items')->insert([
                'inventory_id' => $inventoryId,
                'product_id' => $product->id,
                'quantity_on_hand' => 20,
                'reorder_level' => 5,
                'safety_stock' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('delivery_requests')->insert([
                'requested_by_user_id' => $user->id,
                'product_id' => $product->id,
                'requested_qty' => 5,
                'reason' => 'Replenish stock',
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->withToken($token)->getJson('/api/operations/inventory?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');

        $this->withToken($token)->getJson('/api/operations/requisitions?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');

        $this->createOrder($user, 'ORD-OPS-1', Order::STATUS_PENDING);
        $this->createOrder($user, 'ORD-OPS-2', Order::STATUS_CONFIRMED);

        $this->withToken($token)->getJson('/api/operations/supplier-orders?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');

        $this->withToken($token)->getJson('/api/operations/fulfillment-tasks?per_page=1')
            ->assertOk()
            ->assertJsonPath('per_page', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data');
    }

    private function seededSuperAdmin(): User
    {
        $this->seed(AdminAccessSeeder::class);

        return User::query()
            ->where('email', config('admin_access.super_admin.email'))
            ->firstOrFail();
    }

    private function superAdminToken(): string
    {
        return $this->seededSuperAdmin()
            ->createToken('super-admin')
            ->plainTextToken;
    }

    private function createCategory(?string $name = null): Category
    {
        $sequence = self::$sequence++;

        return Category::query()->create([
            'name' => $name ?? "Category {$sequence}",
            'description' => 'Test category',
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function createSupplier(?string $code = null, ?string $name = null): Supplier
    {
        $sequence = self::$sequence++;

        return Supplier::query()->create([
            'supplier_code' => $code ?? "SUP-{$sequence}",
            'name' => $name ?? "Supplier {$sequence}",
            'phone' => sprintf('09%08d', $sequence),
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function createRegion(?string $slug = null, ?string $name = null): Region
    {
        $sequence = self::$sequence++;

        return Region::query()->create([
            'slug' => $slug ?? "region-{$sequence}",
            'name' => $name ?? "Region {$sequence}",
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createProduct(array $overrides = []): Product
    {
        $sequence = self::$sequence++;

        return Product::query()->create([
            'category_id' => $overrides['category_id'] ?? $this->createCategory()->id,
            'supplier_id' => $overrides['supplier_id'] ?? $this->createSupplier()->id,
            'region_id' => $overrides['region_id'] ?? null,
            'sku' => $overrides['sku'] ?? "SKU-{$sequence}",
            'name' => $overrides['name'] ?? "Product {$sequence}",
            'description' => $overrides['description'] ?? 'Test product',
            'sale_price' => $overrides['sale_price'] ?? '100.00',
            'stock_quantity' => $overrides['stock_quantity'] ?? 10,
            'is_active' => $overrides['is_active'] ?? true,
            'is_deleted' => $overrides['is_deleted'] ?? false,
        ]);
    }

    private function createPost(User $author, string $title, string $status = Post::STATUS_PUBLISHED): Post
    {
        return Post::query()->create([
            'created_by_user_id' => $author->id,
            'title' => $title,
            'excerpt' => 'Post excerpt',
            'body' => 'Post body',
            'status' => $status,
            'published_at' => $status === Post::STATUS_PUBLISHED ? now() : null,
        ]);
    }

    private function createOrder(
        User $user,
        string $orderNo,
        string $status = Order::STATUS_PENDING
    ): Order {
        return Order::query()->create([
            'user_id' => $user->id,
            'order_no' => $orderNo,
            'recipient_name' => $user->full_name,
            'recipient_phone' => $user->phone,
            'shipping_address' => '123 Test Street',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => $status,
            'subtotal' => '100.00',
            'shipping_fee' => '0.00',
            'discount_amount' => '0.00',
            'total_amount' => '100.00',
            'stock_deducted' => false,
        ]);
    }
}
