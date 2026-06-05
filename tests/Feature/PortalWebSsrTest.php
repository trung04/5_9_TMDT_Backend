<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Region;
use App\Models\ShippingCarrier;
use App\Models\SupportTicket;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PortalWebSsrTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_operations_portals(): void
    {
        $this->get('/supplier/inventory')
            ->assertRedirectContains('/login')
            ->assertRedirectContains('redirect=');

        $this->get('/warehouse/inventory')
            ->assertRedirectContains('/login')
            ->assertRedirectContains('redirect=');
    }

    public function test_supplier_can_login_and_render_supplier_pages(): void
    {
        $supplierUser = User::factory()->create([
            'role' => User::ROLE_SUPPLIER,
            'email' => 'supplier-user@example.com',
        ]);
        $this->seedOperationsData();

        $this->post('/login', [
            'email' => $supplierUser->email,
            'password' => 'password123',
        ])->assertRedirect(route('user-web.supplier.inventory'));

        $this->assertAuthenticatedAs($supplierUser, 'web');

        $this->actingAs($supplierUser, 'web')->get('/supplier/inventory')->assertOk()->assertSee('Tồn kho nhà cung cấp')->assertSee('PORTAL-SKU');
        $this->actingAs($supplierUser, 'web')->get('/supplier/requisitions')->assertOk()->assertSee('Phiếu yêu cầu nhà cung cấp');
        $this->actingAs($supplierUser, 'web')->get('/supplier/processing')->assertOk()->assertSee('Nhà cung cấp xử lý đơn');
        $this->actingAs($supplierUser, 'web')->get('/supplier/orders')->assertOk()->assertSee('Danh sách đơn mua vào');
        $this->actingAs($supplierUser, 'web')->get('/supplier/help')->assertOk()->assertSee('Hỗ trợ nhà cung cấp');
    }

    public function test_warehouse_can_login_and_render_warehouse_pages(): void
    {
        $warehouseUser = User::factory()->create([
            'role' => User::ROLE_WAREHOUSE_STAFF,
            'email' => 'warehouse-user@example.com',
        ]);
        $this->seedOperationsData();

        $this->post('/login', [
            'email' => $warehouseUser->email,
            'password' => 'password123',
            'redirect' => '/warehouse/fulfillment',
        ])->assertRedirect('/warehouse/fulfillment');

        $this->actingAs($warehouseUser, 'web')->get('/warehouse/inventory')->assertOk()->assertSee('Tồn kho kho vận')->assertSee('PORTAL-SKU');
        $this->actingAs($warehouseUser, 'web')->get('/warehouse/requisitions')->assertOk()->assertSee('Phiếu tái nhập kho');
        $this->actingAs($warehouseUser, 'web')->get('/warehouse/fulfillment')->assertOk()->assertSee('Fulfillment kho');
        $this->actingAs($warehouseUser, 'web')->get('/warehouse/supplier-orders')->assertOk()->assertSee('Đơn nhà cung cấp tại kho');
        $this->actingAs($warehouseUser, 'web')->get('/warehouse/help')->assertOk()->assertSee('Hỗ trợ kho');
    }

    public function test_portal_actions_persist_to_database(): void
    {
        $warehouseUser = User::factory()->create([
            'role' => User::ROLE_WAREHOUSE_STAFF,
        ]);
        $product = $this->seedOperationsData();

        $this->actingAs($warehouseUser, 'web')
            ->post('/operations/requisitions', [
                'product_id' => $product->id,
                'requested_qty' => 7,
                'reason' => 'SSR portal request',
            ])
            ->assertRedirect();

        $requestId = DB::table('delivery_requests')->where('requested_qty', 7)->value('id');
        $this->assertNotNull($requestId);

        $this->actingAs($warehouseUser, 'web')
            ->patch("/operations/requisitions/{$requestId}/status", [
                'status' => 'approved',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('delivery_requests', [
            'id' => $requestId,
            'status' => 'APPROVED',
            'approved_by_user_id' => $warehouseUser->id,
        ]);

        $this->actingAs($warehouseUser, 'web')
            ->post('/support-tickets/warehouse', [
                'subject' => 'SSR warehouse ticket',
                'message' => 'Need operations support',
            ])
            ->assertRedirect();

        $ticket = SupportTicket::query()->where('subject', 'SSR warehouse ticket')->firstOrFail();

        $this->actingAs($warehouseUser, 'web')
            ->patch("/support-tickets/{$ticket->id}/resolve")
            ->assertRedirect();

        $this->assertSame(SupportTicket::STATUS_RESOLVED, $ticket->refresh()->status);
    }

    public function test_wrong_roles_are_kept_out_of_portals(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);
        $supplierUser = User::factory()->create([
            'role' => User::ROLE_SUPPLIER,
        ]);

        $this->actingAs($customer, 'web')
            ->get('/supplier/inventory')
            ->assertRedirect(route('user-web.unauthorized'));

        $this->actingAs($supplierUser, 'web')
            ->get('/warehouse/inventory')
            ->assertRedirect(route('user-web.unauthorized'));
    }

    private function seedOperationsData(): Product
    {
        $category = Category::query()->create([
            'name' => 'Portal Category',
            'description' => 'Portal category',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $supplier = Supplier::query()->create([
            'supplier_code' => 'PORTAL-SUP',
            'name' => 'Portal Supplier',
            'phone' => '0900000001',
            'email' => 'portal-supplier@example.com',
            'address' => 'Portal supplier location',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $region = Region::query()->create([
            'slug' => 'portal-region',
            'name' => 'Portal Region',
            'description' => 'Portal region',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'region_id' => $region->id,
            'sku' => 'PORTAL-SKU',
            'slug' => 'portal-sku',
            'name' => 'Portal Product',
            'description' => 'Portal product',
            'sale_price' => 100000,
            'stock_quantity' => 3,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        DB::table('inventories')->insert([
            'id' => 1,
            'name' => 'Main Warehouse',
            'location' => 'Ha Noi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('inventory_items')->insert([
            'inventory_id' => 1,
            'product_id' => $product->id,
            'quantity_on_hand' => 3,
            'reorder_level' => 5,
            'safety_stock' => 2,
            'last_counted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('prices')->insert([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'cost_price' => '65000.00',
            'effective_from' => now(),
            'effective_to' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requestUser = User::factory()->create([
            'role' => User::ROLE_WAREHOUSE_STAFF,
        ]);
        DB::table('delivery_requests')->insert([
            'requested_by_user_id' => $requestUser->id,
            'product_id' => $product->id,
            'requested_qty' => 5,
            'reason' => 'Initial portal request',
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'full_name' => 'Portal Customer',
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_no' => 'PORTAL-ORDER-001',
            'recipient_name' => 'Portal Customer',
            'recipient_phone' => '0900000002',
            'shipping_address' => '12 Portal Street',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => Order::STATUS_SHIPPED,
            'subtotal' => '100000.00',
            'shipping_fee' => '35000.00',
            'discount_amount' => '0.00',
            'total_amount' => '135000.00',
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
            'transaction_code' => 'PORTAL-PAY-001',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
            'amount' => '135000.00',
        ]);
        $carrier = ShippingCarrier::query()->create([
            'code' => 'PORTAL-CARRIER',
            'name' => 'Portal Carrier',
            'provider' => ShippingCarrier::PROVIDER_MANUAL,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        DB::table('order_shipments')->insert([
            'order_id' => $order->id,
            'shipping_carrier_id' => $carrier->id,
            'provider' => $carrier->provider,
            'status' => 'created',
            'tracking_code' => 'PORTAL-TRACK-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SupportTicket::query()->create([
            'user_id' => $requestUser->id,
            'subject' => 'Initial supplier ticket',
            'message' => 'Initial ticket',
            'channel' => 'SUPPLIER',
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return $product;
    }
}
