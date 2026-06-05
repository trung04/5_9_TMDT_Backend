<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Product;
use App\Models\Region;
use App\Models\Supplier;
use App\Models\User;
use App\Services\GuestCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserWebSsrTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_render_public_customer_pages(): void
    {
        $product = $this->createProduct(['name' => 'SSR Coffee', 'slug' => 'ssr-coffee']);
        Post::query()->create([
            'created_by_user_id' => User::factory()->create(['role' => User::ROLE_ADMIN])->id,
            'title' => 'SSR Story',
            'body' => 'A public storefront story.',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get('/')->assertOk()->assertSee('Heritage Harvest');
        $this->get('/products')->assertOk()->assertSee('SSR Coffee');
        $this->get('/products/'.$product->slug)->assertOk()->assertSee('SSR Coffee');
        $this->get('/story')->assertOk()->assertSee('SSR Story');
        $this->get('/regions')->assertOk()->assertSee('Hành trình nguồn gốc');
        $this->get('/login')->assertOk()->assertSee('Đăng nhập');
        $this->get('/register')->assertOk()->assertSee('Đăng ký');
    }

    public function test_guest_is_redirected_from_customer_only_pages(): void
    {
        $this->get('/account/profile')
            ->assertRedirectContains('/login');

        $this->get('/checkout')
            ->assertOk()
            ->assertSee('Giỏ hàng');
    }

    public function test_guest_cart_renders_checkout_redirects_on_submit_and_merges_after_login(): void
    {
        $product = $this->createProduct([
            'name' => 'Guest Cart Product',
            'stock_quantity' => 9,
            'sale_price' => 110000,
        ]);
        $customer = User::factory()->create([
            'email' => 'merge@example.com',
        ]);

        $this->post('/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])
            ->assertRedirect()
            ->assertSessionHas(GuestCartService::SESSION_KEY.'.'.$product->id, 2);

        $this->get('/checkout')
            ->assertOk()
            ->assertSee('Guest Cart Product')
            ->assertSee('Đăng nhập để đặt hàng');

        $this->patch('/cart/items/'.$product->id, [
            'quantity' => 3,
        ])
            ->assertRedirect()
            ->assertSessionHas(GuestCartService::SESSION_KEY.'.'.$product->id, 3);

        $this->post('/checkout', [
            'recipient_name' => 'Guest',
            'recipient_phone' => '0900000000',
            'shipping_line1' => '12 SSR Street',
            'shipping_address' => '12 SSR Street, Ha Noi',
            'payment_method' => Order::PAYMENT_METHOD_COD,
        ])
            ->assertRedirectContains('/login');

        $this->post('/login', [
            'email' => $customer->email,
            'password' => 'password123',
        ])->assertRedirect(route('user-web.account.profile'));

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
        $this->assertFalse(session()->has(GuestCartService::SESSION_KEY));
    }

    public function test_customer_can_login_register_and_logout_with_web_session(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer@example.com',
        ]);

        $this->post('/login', [
            'email' => $customer->email,
            'password' => 'password123',
        ])->assertRedirect(route('user-web.account.profile'));

        $this->assertAuthenticatedAs($customer, 'web');

        $this->post('/logout')->assertRedirect(route('user-web.home'));
        $this->assertGuest('web');

        $this->post('/register', [
            'full_name' => 'New Customer',
            'email' => 'new@example.com',
            'phone' => '0900000999',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('user-web.account.profile'));

        $this->assertAuthenticated('web');
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_customer_can_use_cart_and_checkout_bank_transfer(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct(['name' => 'Checkout Product', 'stock_quantity' => 8, 'sale_price' => 150000]);

        $this->actingAs($customer, 'web')
            ->post('/cart/items', [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($customer, 'web')
            ->get('/checkout')
            ->assertOk()
            ->assertSee('Checkout Product');

        $response = $this->actingAs($customer, 'web')
            ->post('/checkout', [
                'recipient_name' => 'Nguyen Van A',
                'recipient_phone' => '0900000000',
                'shipping_line1' => '12 SSR Street',
                'shipping_address' => '12 SSR Street, Ha Noi',
                'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
            ]);

        $order = Order::query()->where('user_id', $customer->id)->firstOrFail();
        $response->assertRedirect(route('user-web.checkout.success', $order->id));

        $this->actingAs($customer, 'web')
            ->get('/checkout/success/'.$order->id)
            ->assertOk()
            ->assertSee('Hướng dẫn chuyển khoản')
            ->assertSee($order->order_no);

        $this->actingAs($customer, 'web')
            ->patch("/checkout/success/{$order->id}/bank-transfer-submitted", [
                'note' => 'Submitted',
            ])
            ->assertRedirect();

        $this->assertTrue((bool) $order->refresh()->payment->raw_payload['customer_transfer_submitted']);
    }

    public function test_customer_account_actions_render_and_persist(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct(['name' => 'Wishlist Product']);
        $order = $this->createOrder($customer, $product);
        $notification = Notification::query()->create([
            'user_id' => $customer->id,
            'title' => 'SSR Notice',
            'message' => 'A customer notification.',
            'channel' => Notification::CHANNEL_SYSTEM,
            'status' => Notification::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $this->actingAs($customer, 'web')
            ->get('/account/profile')
            ->assertOk()
            ->assertSee($customer->full_name);

        $this->actingAs($customer, 'web')
            ->put('/account/profile', [
                'name' => 'Updated Customer',
                'phone' => '0901111222',
                'address' => 'Updated address',
                'city' => 'Ha Noi',
                'favorite_region' => 'North',
                'newsletter' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'full_name' => 'Updated Customer',
            'phone' => '0901111222',
        ]);

        $this->actingAs($customer->refresh(), 'web')
            ->post('/account/addresses', [
                'label' => 'Home',
                'recipient' => 'Updated Customer',
                'phone' => '0901111222',
                'line1' => '12 Address',
                'city' => 'Ha Noi',
                'is_default' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $customer->id,
            'label' => 'Home',
        ]);

        $this->actingAs($customer->refresh(), 'web')
            ->patch('/account/security/password', [
                'current_password' => 'password123',
                'new_password' => 'new-password123',
                'new_password_confirmation' => 'new-password123',
            ])
            ->assertRedirect();

        $this->actingAs($customer->refresh(), 'web')
            ->post('/account/wishlist/items', [
                'product_id' => $product->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($customer->refresh(), 'web')
            ->get('/account/orders/'.$order->id)
            ->assertOk()
            ->assertSee($order->order_no);

        $this->actingAs($customer->refresh(), 'web')
            ->patch('/account/notifications/'.$notification->id.'/read')
            ->assertRedirect();

        $this->assertNotNull($notification->refresh()->read_at);
    }

    public function test_customer_can_cancel_and_confirm_order_actions(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct(['stock_quantity' => 5]);
        $pendingOrder = $this->createOrder($customer, $product, ['status' => Order::STATUS_PENDING]);

        $this->actingAs($customer, 'web')
            ->patch('/account/orders/'.$pendingOrder->id.'/cancel', [
                'reason' => 'Không còn nhu cầu',
            ])
            ->assertRedirect();

        $this->assertSame(Order::STATUS_CANCELLED, $pendingOrder->refresh()->status);

        $shippedOrder = $this->createOrder($customer, $product, [
            'status' => Order::STATUS_SHIPPED,
            'payment_method' => Order::PAYMENT_METHOD_COD,
        ]);

        $this->actingAs($customer, 'web')
            ->patch('/account/orders/'.$shippedOrder->id.'/confirm-delivery')
            ->assertRedirect();

        $this->assertSame(Order::STATUS_DELIVERED, $shippedOrder->refresh()->status);
    }

    public function test_admin_cannot_access_customer_web_area(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin, 'web')
            ->get('/account/profile')
            ->assertRedirect(route('user-web.login'));

        $this->assertGuest('web');
    }

    private function createProduct(array $attributes = []): Product
    {
        $category = Category::query()->create([
            'name' => 'SSR Category',
            'description' => 'SSR category',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $supplier = Supplier::query()->create([
            'supplier_code' => 'SUP-'.strtoupper(substr(uniqid(), -5)),
            'name' => 'SSR Supplier',
            'phone' => '0900000001',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $region = Region::query()->create([
            'slug' => 'ssr-region-'.strtolower(substr(uniqid(), -6)),
            'name' => 'SSR Region',
            'description' => 'SSR region',
            'is_active' => true,
        ]);

        return Product::query()->create(array_merge([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'region_id' => $region->id,
            'sku' => 'SKU-'.strtoupper(substr(uniqid(), -6)),
            'slug' => 'ssr-product-'.strtolower(substr(uniqid(), -6)),
            'name' => 'SSR Product',
            'description' => 'SSR product description',
            'sale_price' => 100000,
            'stock_quantity' => 10,
            'is_active' => true,
            'is_deleted' => false,
        ], $attributes));
    }

    private function createOrder(User $customer, Product $product, array $attributes = []): Order
    {
        $order = Order::query()->create(array_merge([
            'user_id' => $customer->id,
            'order_no' => 'ORD-SSR-'.strtoupper(substr(uniqid(), -6)),
            'recipient_name' => $customer->full_name,
            'recipient_phone' => $customer->phone,
            'shipping_address' => '12 Test Street',
            'payment_method' => $attributes['payment_method'] ?? Order::PAYMENT_METHOD_COD,
            'status' => Order::STATUS_PENDING,
            'subtotal' => '100000.00',
            'shipping_fee' => '20000.00',
            'discount_amount' => '0.00',
            'total_amount' => '120000.00',
            'stock_deducted' => false,
        ], $attributes));

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'quantity' => 1,
            'unit_price' => '100000.00',
            'line_total' => '100000.00',
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'transaction_code' => 'PAY-SSR-'.strtoupper(substr(uniqid(), -6)),
            'payment_method' => $order->payment_method,
            'payment_status' => Payment::STATUS_PENDING,
            'amount' => $order->total_amount,
            'raw_payload' => [
                'instructions' => 'Test payment',
                'customer_transfer_submitted' => false,
            ],
        ]);

        return $order->refresh()->load(['items', 'payment']);
    }
}
