<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence = 1;

    public function test_customer_can_get_an_empty_cart_and_it_is_created_automatically(): void
    {
        [$user, $token] = $this->authenticateCustomer();

        $response = $this->withToken($token)->getJson('/api/cart');

        $response->assertOk()
            ->assertJsonPath('message', 'Cart retrieved successfully.')
            ->assertJsonPath('data.status', Cart::STATUS_ACTIVE)
            ->assertJsonPath('data.item_count', 0)
            ->assertJsonPath('data.total_quantity', 0)
            ->assertJsonPath('data.subtotal', '0.00')
            ->assertJsonCount(0, 'data.items');

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'status' => Cart::STATUS_ACTIVE,
        ]);
    }

    public function test_non_customer_roles_are_blocked_from_cart_and_order_endpoints(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_WAREHOUSE_STAFF, User::ROLE_SUPPLIER] as $role) {
            [$user, $token] = $this->authenticateUserWithRole($role);

            $cartResponse = $this->withToken($token)->getJson('/api/cart');
            $cartResponse->assertStatus(403)
                ->assertJsonPath('message', 'You do not have permission to access this resource.');

            $checkoutResponse = $this->withToken($token)->postJson('/api/orders/checkout', [
                'recipient_name' => $user->full_name,
                'recipient_phone' => $user->phone,
                'shipping_address' => '123 Example Street',
            ]);

            $checkoutResponse->assertStatus(403)
                ->assertJsonPath('message', 'You do not have permission to access this resource.');
        }
    }

    public function test_add_item_creates_cart_item_and_computes_totals_correctly(): void
    {
        [, $token] = $this->authenticateCustomer();
        $product = $this->createProduct(price: 125.50, stock: 5);

        $response = $this->withToken($token)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Cart item saved successfully.')
            ->assertJsonPath('data.item_count', 1)
            ->assertJsonPath('data.total_quantity', 2)
            ->assertJsonPath('data.subtotal', '251.00')
            ->assertJsonPath('data.items.0.product_id', $product->id)
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.unit_price', '125.50')
            ->assertJsonPath('data.items.0.line_total', '251.00');

        $cartItem = CartItem::query()->firstOrFail();
        $this->assertSame(2, $cartItem->quantity);
        $this->assertSame('125.50', $cartItem->unit_price);
        $this->assertSame('251.00', $cartItem->line_total);
    }

    public function test_adding_same_product_twice_increments_the_existing_cart_line(): void
    {
        [, $token] = $this->authenticateCustomer();
        $product = $this->createProduct(price: 99.99, stock: 10);

        $this->withToken($token)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();

        $response = $this->withToken($token)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.item_count', 1)
            ->assertJsonPath('data.total_quantity', 3)
            ->assertJsonPath('data.items.0.quantity', 3)
            ->assertJsonPath('data.items.0.line_total', '299.97');

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_patch_cart_item_updates_quantity_and_totals(): void
    {
        [$user, $token] = $this->authenticateCustomer();
        $product = $this->createProduct(price: 50.00, stock: 10);
        $cartItem = $this->createCartItemForUser($user, $product, quantity: 1);

        $response = $this->withToken($token)->patchJson("/api/cart/items/{$cartItem->id}", [
            'quantity' => 4,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Cart item updated successfully.')
            ->assertJsonPath('data.total_quantity', 4)
            ->assertJsonPath('data.subtotal', '200.00')
            ->assertJsonPath('data.items.0.quantity', 4)
            ->assertJsonPath('data.items.0.unit_price', '50.00')
            ->assertJsonPath('data.items.0.line_total', '200.00');

        $cartItem->refresh();
        $this->assertSame(4, $cartItem->quantity);
        $this->assertSame('200.00', $cartItem->line_total);
    }

    public function test_delete_cart_item_removes_only_the_current_users_own_item(): void
    {
        [$owner] = $this->authenticateCustomer();
        [$otherUser] = $this->authenticateCustomer();
        $product = $this->createProduct();
        $cartItem = $this->createCartItemForUser($owner, $product, quantity: 2);

        Sanctum::actingAs($otherUser);

        $otherResponse = $this->deleteJson("/api/cart/items/{$cartItem->id}");
        $otherResponse->assertStatus(404)
            ->assertJsonPath('message', 'Cart item not found.');

        Sanctum::actingAs($owner);

        $ownerResponse = $this->deleteJson("/api/cart/items/{$cartItem->id}");
        $ownerResponse->assertOk()
            ->assertJsonPath('message', 'Cart item removed successfully.')
            ->assertJsonPath('data.item_count', 0)
            ->assertJsonPath('data.total_quantity', 0);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_add_item_fails_when_quantity_exceeds_stock(): void
    {
        [, $token] = $this->authenticateCustomer();
        $product = $this->createProduct(stock: 2);

        $response = $this->withToken($token)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_add_item_fails_when_product_is_inactive(): void
    {
        [, $token] = $this->authenticateCustomer();
        $product = $this->createProduct(active: false);

        $response = $this->withToken($token)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_update_item_fails_when_quantity_exceeds_stock(): void
    {
        [$user, $token] = $this->authenticateCustomer();
        $product = $this->createProduct(stock: 2);
        $cartItem = $this->createCartItemForUser($user, $product, quantity: 1);

        $response = $this->withToken($token)->patchJson("/api/cart/items/{$cartItem->id}", [
            'quantity' => 4,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        $cartItem->refresh();
        $this->assertSame(1, $cartItem->quantity);
    }

    public function test_checkout_creates_order_items_payment_history_and_updates_stock(): void
    {
        [$user, $token] = $this->authenticateCustomer();
        $productA = $this->createProduct(price: 120.00, stock: 5);
        $productB = $this->createProduct(price: 80.00, stock: 3);
        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'status' => Cart::STATUS_ACTIVE,
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $productA->id,
            'quantity' => 2,
            'unit_price' => '100.00',
            'line_total' => '200.00',
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $productB->id,
            'quantity' => 1,
            'unit_price' => '70.00',
            'line_total' => '70.00',
        ]);

        $response = $this->withToken($token)->postJson('/api/orders/checkout', [
            'recipient_name' => 'Customer One',
            'recipient_phone' => '0900000001',
            'shipping_address' => '123 Nguyen Trai, Ha Noi',
            'note' => 'Call before delivery',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Order created successfully.')
            ->assertJsonPath('data.payment_method', Order::PAYMENT_METHOD_COD)
            ->assertJsonPath('data.status', Order::STATUS_PENDING)
            ->assertJsonPath('data.subtotal', '320.00')
            ->assertJsonPath('data.total_amount', '320.00')
            ->assertJsonPath('data.payment.payment_status', Payment::STATUS_PENDING)
            ->assertJsonPath('data.items.0.unit_price', '120.00')
            ->assertJsonPath('data.items.0.line_total', '240.00')
            ->assertJsonPath('data.items.1.unit_price', '80.00')
            ->assertJsonPath('data.status_history.0.to_status', Order::STATUS_PENDING);

        $order = Order::query()->firstOrFail();
        $this->assertSame(sprintf('ORD-%s%04d', now()->format('Y'), $order->id), $order->order_no);
        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
        ]);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => Order::STATUS_PENDING,
        ]);

        $productA->refresh();
        $productB->refresh();
        $cart->refresh();

        $this->assertSame(3, $productA->stock_quantity);
        $this->assertSame(2, $productB->stock_quantity);
        $this->assertSame(Cart::STATUS_CHECKED_OUT, $cart->status);
    }

    public function test_checkout_fails_atomically_for_an_empty_cart(): void
    {
        [$user, $token] = $this->authenticateCustomer();
        Cart::query()->create([
            'user_id' => $user->id,
            'status' => Cart::STATUS_ACTIVE,
        ]);

        $response = $this->withToken($token)->postJson('/api/orders/checkout', [
            'recipient_name' => 'Customer One',
            'recipient_phone' => '0900000001',
            'shipping_address' => '123 Nguyen Trai, Ha Noi',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cart']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'status' => Cart::STATUS_ACTIVE,
        ]);
    }

    public function test_checkout_fails_atomically_when_product_becomes_inactive(): void
    {
        [$user, $token] = $this->authenticateCustomer();
        $product = $this->createProduct(price: 120.00, stock: 5);
        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'status' => Cart::STATUS_ACTIVE,
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => '120.00',
            'line_total' => '120.00',
        ]);

        $product->update(['is_active' => false]);

        $response = $this->withToken($token)->postJson('/api/orders/checkout', [
            'recipient_name' => 'Customer One',
            'recipient_phone' => '0900000001',
            'shipping_address' => '123 Nguyen Trai, Ha Noi',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cart']);

        $this->assertDatabaseCount('orders', 0);
        $cart->refresh();
        $product->refresh();
        $this->assertSame(Cart::STATUS_ACTIVE, $cart->status);
        $this->assertSame(5, $product->stock_quantity);
    }

    public function test_checkout_fails_atomically_when_stock_is_insufficient(): void
    {
        [$user, $token] = $this->authenticateCustomer();
        $product = $this->createProduct(price: 120.00, stock: 2);
        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'status' => Cart::STATUS_ACTIVE,
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => '120.00',
            'line_total' => '360.00',
        ]);

        $response = $this->withToken($token)->postJson('/api/orders/checkout', [
            'recipient_name' => 'Customer One',
            'recipient_phone' => '0900000001',
            'shipping_address' => '123 Nguyen Trai, Ha Noi',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cart']);

        $this->assertDatabaseCount('orders', 0);
        $cart->refresh();
        $product->refresh();
        $this->assertSame(Cart::STATUS_ACTIVE, $cart->status);
        $this->assertSame(2, $product->stock_quantity);
    }

    public function test_order_list_returns_only_the_current_users_orders_with_pagination(): void
    {
        [$user, $token] = $this->authenticateCustomer();
        $otherUser = User::factory()->create();
        $firstOrder = $this->createOrderForUser($user, totalAmount: 100.00);
        $secondOrder = $this->createOrderForUser($user, totalAmount: 200.00);
        $thirdOrder = $this->createOrderForUser($otherUser, totalAmount: 999.00);

        $response = $this->withToken($token)->getJson('/api/orders?per_page=1&page=1');

        $response->assertOk()
            ->assertJsonPath('message', 'Orders retrieved successfully.')
            ->assertJsonPath('pagination.total', 2)
            ->assertJsonPath('pagination.per_page', 1)
            ->assertJsonCount(1, 'data');

        $returnedOrderId = $response->json('data.0.id');
        $this->assertContains($returnedOrderId, [$firstOrder->id, $secondOrder->id]);
        $this->assertNotSame($thirdOrder->id, $returnedOrderId);
    }

    public function test_order_detail_returns_only_the_current_users_order_with_nested_data(): void
    {
        [$user, $token] = $this->authenticateCustomer();
        $otherUser = User::factory()->create();
        $ownOrder = $this->createOrderForUser($user, totalAmount: 150.00);
        $otherOrder = $this->createOrderForUser($otherUser, totalAmount: 250.00);

        $ownResponse = $this->withToken($token)->getJson("/api/orders/{$ownOrder->id}");
        $ownResponse->assertOk()
            ->assertJsonPath('message', 'Order retrieved successfully.')
            ->assertJsonPath('data.id', $ownOrder->id)
            ->assertJsonPath('data.payment.payment_status', Payment::STATUS_PENDING)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonCount(1, 'data.status_history');

        $otherResponse = $this->withToken($token)->getJson("/api/orders/{$otherOrder->id}");
        $otherResponse->assertStatus(404)
            ->assertJsonPath('message', 'Order not found.');
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function authenticateCustomer(): array
    {
        return $this->authenticateUserWithRole(User::ROLE_CUSTOMER);
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function authenticateUserWithRole(string $role): array
    {
        $user = User::factory()->create([
            'role' => $role,
        ]);
        $token = $user->createToken('test_token')->plainTextToken;

        return [$user, $token];
    }

    private function createProduct(float $price = 100.00, int $stock = 10, bool $active = true): Product
    {
        $sequence = self::$sequence++;
        $category = Category::query()->create([
            'name' => "Category {$sequence}",
            'description' => 'Test category',
            'is_active' => true,
        ]);

        $supplier = Supplier::query()->create([
            'supplier_code' => "SUP{$sequence}",
            'name' => "Supplier {$sequence}",
            'contact_name' => "Contact {$sequence}",
            'phone' => sprintf('09%08d', $sequence),
            'email' => "supplier{$sequence}@example.com",
            'address' => '123 Supplier Street',
            'is_active' => true,
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => "SKU-{$sequence}",
            'name' => "Product {$sequence}",
            'description' => 'Test product',
            'sale_price' => number_format($price, 2, '.', ''),
            'stock_quantity' => $stock,
            'is_active' => $active,
        ]);
    }

    private function createCartItemForUser(User $user, Product $product, int $quantity = 1): CartItem
    {
        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'status' => Cart::STATUS_ACTIVE,
        ]);

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->sale_price,
            'line_total' => number_format($quantity * (float) $product->sale_price, 2, '.', ''),
        ]);
    }

    private function createOrderForUser(User $user, float $totalAmount = 100.00): Order
    {
        $product = $this->createProduct(price: $totalAmount, stock: 10);
        $sequence = self::$sequence++;
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_no' => "ORD-TEST-{$sequence}",
            'recipient_name' => $user->full_name,
            'recipient_phone' => $user->phone,
            'shipping_address' => '123 Test Street',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => Order::STATUS_PENDING,
            'subtotal' => number_format($totalAmount, 2, '.', ''),
            'shipping_fee' => '0.00',
            'discount_amount' => '0.00',
            'total_amount' => number_format($totalAmount, 2, '.', ''),
            'note' => null,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'quantity' => 1,
            'unit_price' => number_format($totalAmount, 2, '.', ''),
            'line_total' => number_format($totalAmount, 2, '.', ''),
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'transaction_code' => null,
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
            'amount' => number_format($totalAmount, 2, '.', ''),
            'gateway_name' => null,
            'gateway_reference' => null,
            'paid_at' => null,
            'raw_payload' => null,
        ]);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'changed_by_user_id' => $user->id,
            'from_status' => null,
            'to_status' => Order::STATUS_PENDING,
            'note' => 'Order created.',
            'changed_at' => now(),
        ]);

        return $order;
    }
}
