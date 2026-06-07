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
use App\Models\ShippingCarrier;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\AdminAccessSeeder;
use Database\Seeders\ShippingCarrierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShippingCarrierGhnApiTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence = 1;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.ghn.token', 'test-token');
        config()->set('services.ghn.shop_id', 123456);
        config()->set('services.ghn.base_url', 'https://online-gateway.ghn.vn/shiip/public-api');
    }

    public function test_super_admin_can_crud_shipping_carriers(): void
    {
        $admin = $this->seededSuperAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/admin/shipping-carriers', [
            'code' => 'FAST',
            'name' => 'Fast Manual',
            'provider' => ShippingCarrier::PROVIDER_MANUAL,
            'tracking_url_template' => 'https://carrier.test/{code}',
            'default_weight' => 1200,
            'default_length' => 25,
            'default_width' => 20,
            'default_height' => 12,
            'is_active' => true,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.code', 'FAST')
            ->assertJsonPath('data.provider', ShippingCarrier::PROVIDER_MANUAL);

        $carrierId = $createResponse->json('data.id');

        $this->withToken($token)->putJson("/api/admin/shipping-carriers/{$carrierId}", [
            'code' => 'FAST',
            'name' => 'Fast Manual Updated',
            'provider' => ShippingCarrier::PROVIDER_MANUAL,
            'default_weight' => 1500,
            'is_active' => true,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Fast Manual Updated')
            ->assertJsonPath('data.default_weight', 1500);

        $this->withToken($token)->deleteJson("/api/admin/shipping-carriers/{$carrierId}")
            ->assertOk()
            ->assertJsonPath('data.is_deleted', true);
    }

    public function test_checkout_stores_ghn_address_snapshot(): void
    {
        [$customer, $token] = $this->authenticateCustomer();
        $product = $this->createProduct(price: 120000, stock: 5);
        $cart = Cart::query()->create([
            'user_id' => $customer->id,
            'status' => Cart::STATUS_ACTIVE,
        ]);
        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->sale_price,
            'line_total' => $product->sale_price,
        ]);

        $response = $this->withToken($token)->postJson('/api/orders/checkout', [
            'recipient_name' => 'Customer One',
            'recipient_phone' => '0900000001',
            'shipping_address' => '12 Nguyen Trai, Phuong Ben Nghe, Quan 1, Ho Chi Minh',
            'shipping_line1' => '12 Nguyen Trai',
            'shipping_province_id' => 202,
            'shipping_province_name' => 'Ho Chi Minh',
            'shipping_district_id' => 1442,
            'shipping_district_name' => 'Quan 1',
            'shipping_ward_code' => '20101',
            'shipping_ward_name' => 'Phuong Ben Nghe',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.shipping_line1', '12 Nguyen Trai')
            ->assertJsonPath('data.shipping_province_id', 202)
            ->assertJsonPath('data.shipping_district_id', 1442)
            ->assertJsonPath('data.shipping_ward_code', '20101');

        $this->assertDatabaseHas('orders', [
            'shipping_line1' => '12 Nguyen Trai',
            'shipping_province_name' => 'Ho Chi Minh',
            'shipping_district_name' => 'Quan 1',
            'shipping_ward_name' => 'Phuong Ben Nghe',
        ]);
    }

    public function test_create_ghn_shipment_saves_tracking_fee_and_moves_order_to_packed(): void
    {
        $admin = $this->seededSuperAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $this->seed(ShippingCarrierSeeder::class);
        $order = $this->createOrder(status: Order::STATUS_CONFIRMED);
        $carrier = ShippingCarrier::query()->where('code', 'GHN')->firstOrFail();
        $carrier->update([
            'pickup_ward_code' => 'HN-CG-01',
            'pickup_district_id' => 1454,
        ]);

        Http::fake([
            'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create' => Http::response([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    'order_code' => 'GHN123456',
                    'total_fee' => 33000,
                    'expected_delivery_time' => '2026-05-24T00:00:00Z',
                ],
            ]),
        ]);

        $response = $this->withToken($token)->postJson("/api/admin/orders/{$order->id}/shipment", [
            'shipping_carrier_id' => $carrier->id,
            'weight' => 1000,
            'length' => 20,
            'width' => 20,
            'height' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', Order::STATUS_PACKED)
            ->assertJsonPath('data.shipping_code', 'GHN123456')
            ->assertJsonPath('data.shipment.tracking_code', 'GHN123456')
            ->assertJsonPath('data.shipment.shipping_fee', '33000.00');

        $this->assertDatabaseHas('order_shipments', [
            'order_id' => $order->id,
            'tracking_code' => 'GHN123456',
            'shipping_fee' => '33000.00',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'shipping_fee' => '20000.00',
            'shipping_code' => 'GHN123456',
        ]);

        Http::assertSent(fn ($request): bool => $request['to_ward_code'] === '20101'
            && $request['to_district_id'] === 1442
            && $request['return_ward_code'] === 'HN-CG-01'
            && $request['return_district_id'] === 1454);
    }

    public function test_create_ghn_shipment_falls_back_to_customer_default_address(): void
    {
        $admin = $this->seededSuperAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $this->seed(ShippingCarrierSeeder::class);
        $order = $this->createOrder(status: Order::STATUS_CONFIRMED);
        $carrier = ShippingCarrier::query()->where('code', 'GHN')->firstOrFail();

        $order->update([
            'shipping_address' => 'Legacy checkout address',
            'shipping_line1' => null,
            'shipping_province_id' => null,
            'shipping_province_name' => null,
            'shipping_district_id' => null,
            'shipping_district_name' => null,
            'shipping_ward_code' => null,
            'shipping_ward_name' => null,
        ]);

        $order->user->addresses()->create([
            'label' => 'Default',
            'recipient' => 'Fallback Receiver',
            'phone' => '0900000099',
            'line1' => '456 Cau Giay',
            'city' => 'Ha Noi',
            'ghn_province_id' => 201,
            'ghn_province_name' => 'Ha Noi',
            'ghn_district_id' => 1454,
            'ghn_district_name' => 'Cau Giay',
            'ghn_ward_code' => 'HN-CG-01',
            'ghn_ward_name' => 'Dich Vong Hau',
            'is_default' => true,
        ]);

        Http::fake([
            'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create' => Http::response([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    'order_code' => 'GHN-FALLBACK',
                    'total_fee' => 25000,
                ],
            ]),
        ]);

        $response = $this->withToken($token)->postJson("/api/admin/orders/{$order->id}/shipment", [
            'shipping_carrier_id' => $carrier->id,
            'weight' => 1000,
            'length' => 20,
            'width' => 20,
            'height' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.shipping_line1', '456 Cau Giay')
            ->assertJsonPath('data.shipping_province_id', 201)
            ->assertJsonPath('data.shipping_district_id', 1454)
            ->assertJsonPath('data.shipping_ward_code', 'HN-CG-01')
            ->assertJsonPath('data.shipment.tracking_code', 'GHN-FALLBACK');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'shipping_address' => '456 Cau Giay, Dich Vong Hau, Cau Giay, Ha Noi',
            'shipping_line1' => '456 Cau Giay',
            'shipping_province_name' => 'Ha Noi',
            'shipping_district_name' => 'Cau Giay',
            'shipping_ward_name' => 'Dich Vong Hau',
        ]);

        Http::assertSent(fn ($request): bool => $request['to_address'] === '456 Cau Giay'
            && $request['to_province_name'] === 'Ha Noi'
            && $request['to_district_name'] === 'Cau Giay'
            && $request['to_ward_name'] === 'Dich Vong Hau');
    }

    public function test_create_shipment_persists_admin_recipient_and_address_overrides(): void
    {
        $admin = $this->seededSuperAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $this->seed(ShippingCarrierSeeder::class);
        $order = $this->createOrder(status: Order::STATUS_CONFIRMED);
        $carrier = ShippingCarrier::query()->where('code', 'GHN')->firstOrFail();

        Http::fake([
            'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create' => Http::response([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    'order_code' => 'GHN-OVERRIDE',
                    'total_fee' => 28000,
                ],
            ]),
        ]);

        $response = $this->withToken($token)->postJson("/api/admin/orders/{$order->id}/shipment", [
            'shipping_carrier_id' => $carrier->id,
            'recipient_name' => 'Override Receiver',
            'recipient_phone' => '0911111222',
            'shipping_line1' => '88 Le Loi',
            'shipping_province_id' => 203,
            'shipping_province_name' => 'Da Nang',
            'shipping_district_id' => 3001,
            'shipping_district_name' => 'Hai Chau',
            'shipping_ward_code' => 'DN-HC-01',
            'shipping_ward_name' => 'Hai Chau 1',
            'weight' => 1200,
            'length' => 25,
            'width' => 18,
            'height' => 12,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.recipient_name', 'Override Receiver')
            ->assertJsonPath('data.recipient_phone', '0911111222')
            ->assertJsonPath('data.shipping_address', '88 Le Loi, Hai Chau 1, Hai Chau, Da Nang')
            ->assertJsonPath('data.shipment.tracking_code', 'GHN-OVERRIDE');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'recipient_name' => 'Override Receiver',
            'recipient_phone' => '0911111222',
            'shipping_line1' => '88 Le Loi',
            'shipping_province_id' => 203,
            'shipping_province_name' => 'Da Nang',
            'shipping_district_id' => 3001,
            'shipping_district_name' => 'Hai Chau',
            'shipping_ward_code' => 'DN-HC-01',
            'shipping_ward_name' => 'Hai Chau 1',
        ]);

        Http::assertSent(fn ($request): bool => $request['to_name'] === 'Override Receiver'
            && $request['to_phone'] === '0911111222'
            && $request['to_address'] === '88 Le Loi'
            && $request['to_province_name'] === 'Da Nang');
    }

    public function test_order_cannot_be_shipped_without_a_shipment(): void
    {
        $admin = $this->seededSuperAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $order = $this->createOrder(status: Order::STATUS_PACKED);

        $this->withToken($token)->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => Order::STATUS_SHIPPED,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_sync_ghn_status_updates_order_status(): void
    {
        $admin = $this->seededSuperAdmin();
        $token = $admin->createToken('test')->plainTextToken;
        $this->seed(ShippingCarrierSeeder::class);
        $order = $this->createOrder(status: Order::STATUS_PACKED);
        $carrier = ShippingCarrier::query()->where('code', 'GHN')->firstOrFail();

        $order->shipment()->create([
            'shipping_carrier_id' => $carrier->id,
            'provider' => ShippingCarrier::PROVIDER_GHN,
            'status' => 'ready_to_pick',
            'tracking_code' => 'GHN123456',
            'tracking_url' => 'https://donhang.ghn.vn/?order_code=GHN123456',
            'service_type_id' => 2,
            'payment_type_id' => 1,
            'required_note' => 'KHONGCHOXEMHANG',
            'weight' => 1000,
            'length' => 20,
            'width' => 20,
            'height' => 10,
            'shipping_fee' => '33000.00',
            'cod_amount' => $order->total_amount,
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);

        Http::fake([
            'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail-by-client-code' => Http::response([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    'order_code' => 'GHN123456',
                    'status' => 'delivered',
                ],
            ]),
        ]);

        $this->withToken($token)->postJson("/api/admin/orders/{$order->id}/shipment/sync")
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_DELIVERED)
            ->assertJsonPath('data.shipment.status', 'delivered');
    }

    /**
     * @return array{0: User, 1: string}
     */
    private function authenticateCustomer(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);

        return [$user, $user->createToken('test')->plainTextToken];
    }

    private function seededSuperAdmin(): User
    {
        $this->seed(AdminAccessSeeder::class);

        return User::query()
            ->where('email', config('admin_access.super_admin.email'))
            ->firstOrFail();
    }

    private function createProduct(float $price = 100000, int $stock = 10): Product
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
            'phone' => sprintf('09%08d', $sequence),
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
            'is_active' => true,
        ]);
    }

    private function createOrder(string $status): Order
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);
        $product = $this->createProduct();
        $sequence = self::$sequence++;
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_no' => "ORD-GHN-{$sequence}",
            'recipient_name' => 'Customer One',
            'recipient_phone' => '0900000001',
            'shipping_address' => '12 Nguyen Trai, Phuong Ben Nghe, Quan 1, Ho Chi Minh',
            'shipping_line1' => '12 Nguyen Trai',
            'shipping_province_id' => 202,
            'shipping_province_name' => 'Ho Chi Minh',
            'shipping_district_id' => 1442,
            'shipping_district_name' => 'Quan 1',
            'shipping_ward_code' => '20101',
            'shipping_ward_name' => 'Phuong Ben Nghe',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => $status,
            'subtotal' => '100000.00',
            'shipping_fee' => '20000.00',
            'discount_amount' => '0.00',
            'total_amount' => '120000.00',
            'stock_deducted' => true,
        ]);

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
            'transaction_code' => null,
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
            'amount' => '120000.00',
            'gateway_name' => null,
            'gateway_reference' => null,
            'paid_at' => null,
            'raw_payload' => null,
        ]);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'changed_by_user_id' => $customer->id,
            'from_status' => null,
            'to_status' => $status,
            'note' => 'Test order status.',
            'changed_at' => now(),
        ]);

        return $order;
    }
}
