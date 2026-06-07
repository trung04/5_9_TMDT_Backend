<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Product;
use App\Models\ShippingCarrier;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminWebSsrTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_admin_login_page(): void
    {
        $this->get('/admin-web/login')
            ->assertOk()
            ->assertSee('Đăng nhập quản trị');
    }

    public function test_admin_can_sign_in_and_open_dashboard(): void
    {
        $this->seed(AdminAccessSeeder::class);

        $response = $this->post('/admin-web/login', [
            'email' => config('admin_access.super_admin.email'),
            'password' => config('admin_access.super_admin.password'),
        ]);

        $response->assertRedirect('/admin-web');

        $this->followRedirects($response)
            ->assertSee('Bảng điều khiển')
            ->assertSee('Đăng xuất')
            ->assertDontSee('Supplier Portal')
            ->assertDontSee('Warehouse Portal');
    }

    public function test_customer_cannot_sign_in_to_admin_web(): void
    {
        $customer = User::factory()->create([
            'email' => 'customer@example.com',
        ]);

        $this->post('/admin-web/login', [
            'email' => $customer->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_dashboard_keeps_datetime_filters_and_shows_active_ranges(): void
    {
        $admin = $this->seededSuperAdmin();

        $this->actingAs($admin, 'web')
            ->get('/admin-web?date_from=2026-06-01T08:30&date_to=2026-06-04T18:15&chart_range=custom')
            ->assertOk()
            ->assertSee('name="date_from" step="60" value="2026-06-01T08:30"', false)
            ->assertSee('name="date_to" step="60" value="2026-06-04T18:15"', false)
            ->assertSee('01/06/2026 08:30')
            ->assertSee('04/06/2026 18:15');
    }

    public function test_dashboard_renders_visual_chart_containers(): void
    {
        $admin = $this->seededSuperAdmin();
        $customer = User::factory()->create([
            'full_name' => 'Chart Customer',
        ]);
        $category = Category::query()->create([
            'name' => 'Chart Category',
            'description' => 'Dashboard chart category',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => 'CHART-SSR-001',
            'name' => 'Chart SSR Product',
            'description' => 'Dashboard chart product',
            'sale_price' => 125000,
            'stock_quantity' => 15,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $order = $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-CHART-SSR',
            'status' => Order::STATUS_DELIVERED,
            'subtotal' => 250000,
            'shipping_fee' => 0,
            'total_amount' => 250000,
            'stock_deducted' => true,
            'delivered_at' => now(),
        ], [
            'payment_status' => Payment::STATUS_SUCCESS,
            'amount' => 250000,
            'paid_at' => now(),
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'quantity' => 2,
            'unit_price' => '125000.00',
            'line_total' => '250000.00',
        ]);

        $this->actingAs($admin, 'web')
            ->get('/admin-web')
            ->assertOk()
            ->assertSee('id="dashboard-revenue-chart"', false)
            ->assertSee('id="dashboard-order-status-chart"', false)
            ->assertSee('id="dashboard-top-products-chart"', false)
            ->assertSee('data-dashboard-chart="revenue"', false)
            ->assertSee('cdn.jsdelivr.net/npm/chart.js', false);
    }

    public function test_dashboard_work_queue_cards_link_for_all_active_admins(): void
    {
        $admin = $this->seededPlainAdmin();

        $this->actingAs($admin, 'web')
            ->get('/admin-web')
            ->assertOk()
            ->assertSee(route('admin-web.orders.index', ['queue' => 'pending_orders']), false)
            ->assertSee(route('admin-web.orders.index', ['queue' => 'bank_transfer_pending']), false)
            ->assertSee('Bấm để xem chi tiết');
    }

    public function test_admin_root_is_available_without_admin_role_or_permissions(): void
    {
        $admin = $this->seededPlainAdmin();

        $this->actingAs($admin, 'web')
            ->get('/admin-web')
            ->assertOk()
            ->assertSee('Bảng điều khiển');
    }

    public function test_removed_access_routes_return_not_found(): void
    {
        $admin = $this->seededSuperAdmin();

        $this->actingAs($admin, 'web')->get('/admin-web/access')->assertNotFound();
        $this->actingAs($admin, 'web')->get('/admin-web/access/roles/create')->assertNotFound();
        $this->actingAs($admin, 'web')->get('/admin-web/access/admins/create')->assertNotFound();
    }

    public function test_admin_can_render_logistics_page_with_orders(): void
    {
        $admin = $this->seededSuperAdmin();
        $customer = User::factory()->create([
            'full_name' => 'SSR Customer',
        ]);
        $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-SSR-001',
            'shipping_address' => '12 SSR Street',
        ]);

        $this->actingAs($admin, 'web')
            ->get('/admin-web/logistics')
            ->assertOk()
            ->assertSee('ORD-SSR-001');
    }

    public function test_logistics_page_can_filter_orders_by_pending_work_queue(): void
    {
        $admin = $this->seededSuperAdmin();
        $customer = User::factory()->create([
            'full_name' => 'Pending Queue Customer',
        ]);

        $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-QUEUE-PENDING',
            'status' => Order::STATUS_PENDING,
        ]);
        $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-QUEUE-CONFIRMED',
            'status' => Order::STATUS_CONFIRMED,
        ]);

        $this->actingAs($admin, 'web')
            ->get('/admin-web/logistics?queue=pending_orders')
            ->assertOk()
            ->assertSee('Đang xem: Đơn mới chờ xác nhận')
            ->assertSee('ORD-QUEUE-PENDING')
            ->assertDontSee('ORD-QUEUE-CONFIRMED');
    }

    public function test_logistics_page_can_filter_orders_by_bank_transfer_pending_work_queue(): void
    {
        $admin = $this->seededSuperAdmin();
        $customer = User::factory()->create([
            'full_name' => 'Bank Queue Customer',
        ]);

        $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-BANK-PENDING',
            'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
            'status' => Order::STATUS_PENDING,
        ], [
            'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_status' => Payment::STATUS_PENDING,
        ]);
        $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-COD-PENDING',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => Order::STATUS_PENDING,
        ], [
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'payment_status' => Payment::STATUS_PENDING,
        ]);
        $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-BANK-DELIVERED',
            'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
            'status' => Order::STATUS_DELIVERED,
        ], [
            'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_status' => Payment::STATUS_PENDING,
        ]);
        $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-BANK-CANCELLED',
            'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
            'status' => Order::STATUS_CANCELLED,
        ], [
            'payment_method' => Order::PAYMENT_METHOD_BANK_TRANSFER,
            'payment_status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'web')
            ->get('/admin-web/logistics?queue=bank_transfer_pending')
            ->assertOk()
            ->assertSee('Đang xem: Đơn chuyển khoản chờ xác nhận')
            ->assertSee('ORD-BANK-PENDING')
            ->assertDontSee('ORD-COD-PENDING')
            ->assertDontSee('ORD-BANK-DELIVERED')
            ->assertDontSee('ORD-BANK-CANCELLED');
    }

    public function test_logistics_pagination_keeps_queue_filter_query_string(): void
    {
        $admin = $this->seededSuperAdmin();
        $customer = User::factory()->create([
            'full_name' => 'Pagination Queue Customer',
        ]);

        $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-PAGE-001',
            'status' => Order::STATUS_PENDING,
        ]);
        $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-PAGE-002',
            'status' => Order::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin, 'web')
            ->get('/admin-web/logistics?queue=pending_orders&per_page=1');

        $response->assertOk()
            ->assertSee('ORD-PAGE-002')
            ->assertDontSee('ORD-PAGE-001');

        $this->assertStringContainsString('queue=pending_orders', $response->getContent());
        $this->assertStringContainsString('per_page=1', $response->getContent());
        $this->assertStringContainsString('page=2', $response->getContent());
    }

    public function test_admin_order_detail_shows_ghn_shipment_form_with_prefilled_address(): void
    {
        $admin = $this->seededSuperAdmin();
        $carrier = $this->createGhnCarrier();
        $customer = User::factory()->create([
            'full_name' => 'GHN Form Customer',
            'phone' => '0900000200',
        ]);
        $order = $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-GHN-WEB-FORM',
            'recipient_name' => 'Web Receiver',
            'recipient_phone' => '0900000201',
            'shipping_address' => '12 Nguyen Trai, Ben Nghe, Quan 1, Ho Chi Minh',
            'shipping_line1' => '12 Nguyen Trai',
            'shipping_province_id' => 202,
            'shipping_province_name' => 'Ho Chi Minh',
            'shipping_district_id' => 1442,
            'shipping_district_name' => 'Quan 1',
            'shipping_ward_code' => 'HCM-Q1-01',
            'shipping_ward_name' => 'Ben Nghe',
            'status' => Order::STATUS_CONFIRMED,
            'stock_deducted' => true,
        ]);

        $this->actingAs($admin, 'web')
            ->get("/admin-web/orders/{$order->id}")
            ->assertOk()
            ->assertSee('data-ghn-shipment-form', false)
            ->assertSee('Tao van don GHN')
            ->assertSee('name="recipient_name"', false)
            ->assertSee('value="Web Receiver"', false)
            ->assertSee('name="shipping_line1"', false)
            ->assertSee('value="'.$carrier->id.'"', false);
    }

    public function test_admin_web_can_create_ghn_shipment_from_order_detail(): void
    {
        config()->set('services.ghn.token', 'test-token');
        config()->set('services.ghn.shop_id', 123456);
        config()->set('services.ghn.base_url', 'https://online-gateway.ghn.vn/shiip/public-api');

        $admin = $this->seededSuperAdmin();
        $carrier = $this->createGhnCarrier();
        $customer = User::factory()->create([
            'full_name' => 'GHN Create Customer',
            'phone' => '0900000300',
        ]);
        $order = $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-GHN-WEB-CREATE',
            'recipient_name' => 'Web GHN Receiver',
            'recipient_phone' => '0900000301',
            'shipping_address' => '88 Le Loi, Hai Chau 1, Hai Chau, Da Nang',
            'shipping_line1' => '88 Le Loi',
            'shipping_province_id' => 203,
            'shipping_province_name' => 'Da Nang',
            'shipping_district_id' => 3001,
            'shipping_district_name' => 'Hai Chau',
            'shipping_ward_code' => 'DN-HC-01',
            'shipping_ward_name' => 'Hai Chau 1',
            'status' => Order::STATUS_CONFIRMED,
            'shipping_fee' => 15000,
            'total_amount' => 115000,
            'stock_deducted' => true,
        ]);

        Http::fake([
            'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create' => Http::response([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    'order_code' => 'GHN-WEB-001',
                    'total_fee' => 45000,
                    'expected_delivery_time' => '2026-06-08T00:00:00Z',
                ],
            ]),
        ]);

        $response = $this->actingAs($admin, 'web')
            ->post("/admin-web/orders/{$order->id}/shipment", [
                'shipping_carrier_id' => $carrier->id,
                'recipient_name' => 'Web GHN Receiver',
                'recipient_phone' => '0900000301',
                'shipping_line1' => '88 Le Loi',
                'shipping_province_id' => 203,
                'shipping_province_name' => 'Da Nang',
                'shipping_district_id' => 3001,
                'shipping_district_name' => 'Hai Chau',
                'shipping_ward_code' => 'DN-HC-01',
                'shipping_ward_name' => 'Hai Chau 1',
                'service_type_id' => 2,
                'payment_type_id' => 1,
                'required_note' => 'KHONGCHOXEMHANG',
                'weight' => 1200,
                'length' => 25,
                'width' => 20,
                'height' => 12,
                'note' => 'Create GHN from admin web.',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin-web.orders.show', $order->id));

        $order->refresh();
        $this->assertSame(Order::STATUS_PACKED, $order->status);
        $this->assertSame('GHN-WEB-001', $order->shipping_code);
        $this->assertSame('15000.00', $order->shipping_fee);
        $this->assertDatabaseHas('order_shipments', [
            'order_id' => $order->id,
            'tracking_code' => 'GHN-WEB-001',
            'shipping_fee' => '45000.00',
            'cod_amount' => '115000.00',
        ]);

        Http::assertSent(fn ($request): bool => $request['to_ward_code'] === 'DN-HC-01'
            && $request['to_district_id'] === 3001
            && $request['return_ward_code'] === 'HN-CG-01'
            && $request['return_district_id'] === 1454);
    }

    public function test_admin_web_can_sync_ghn_shipment_status(): void
    {
        config()->set('services.ghn.token', 'test-token');
        config()->set('services.ghn.shop_id', 123456);
        config()->set('services.ghn.base_url', 'https://online-gateway.ghn.vn/shiip/public-api');

        $admin = $this->seededSuperAdmin();
        $carrier = $this->createGhnCarrier();
        $customer = User::factory()->create([
            'full_name' => 'GHN Sync Customer',
        ]);
        $order = $this->createLogisticsOrder($customer, [
            'order_no' => 'ORD-GHN-WEB-SYNC',
            'status' => Order::STATUS_PACKED,
            'stock_deducted' => true,
            'shipping_code' => 'GHN-WEB-SYNC',
        ]);
        $order->shipment()->create([
            'shipping_carrier_id' => $carrier->id,
            'provider' => ShippingCarrier::PROVIDER_GHN,
            'status' => 'ready_to_pick',
            'tracking_code' => 'GHN-WEB-SYNC',
            'tracking_url' => 'https://donhang.ghn.vn/?order_code=GHN-WEB-SYNC',
            'service_type_id' => 2,
            'payment_type_id' => 1,
            'required_note' => 'KHONGCHOXEMHANG',
            'weight' => 1000,
            'length' => 20,
            'width' => 20,
            'height' => 10,
            'shipping_fee' => '33000.00',
            'cod_amount' => '115000.00',
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
        ]);

        Http::fake([
            'https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail-by-client-code' => Http::response([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    'order_code' => 'GHN-WEB-SYNC',
                    'status' => 'delivered',
                ],
            ]),
        ]);

        $response = $this->actingAs($admin, 'web')
            ->post("/admin-web/orders/{$order->id}/shipment/sync");

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin-web.orders.show', $order->id));
        $this->assertSame(Order::STATUS_DELIVERED, $order->refresh()->status);
        $this->assertDatabaseHas('order_shipments', [
            'order_id' => $order->id,
            'status' => 'delivered',
        ]);
    }

    public function test_admin_web_can_save_ghn_carrier_defaults_and_pickup_address(): void
    {
        $admin = $this->seededSuperAdmin();

        $response = $this->actingAs($admin, 'web')
            ->post('/admin-web/shipping-carriers', [
                'code' => 'GHN-WEB',
                'name' => 'GHN Web',
                'provider' => ShippingCarrier::PROVIDER_GHN,
                'tracking_url_template' => 'https://donhang.ghn.vn/?order_code={code}',
                'default_weight' => 1400,
                'default_length' => 30,
                'default_width' => 22,
                'default_height' => 12,
                'default_service_type_id' => 5,
                'default_payment_type_id' => 2,
                'default_required_note' => 'CHOXEMHANGKHONGTHU',
                'pickup_name' => 'Kho GHN Web',
                'pickup_phone' => '0900000999',
                'pickup_address' => '123 Cau Giay',
                'pickup_province_id' => 201,
                'pickup_province_name' => 'Ha Noi',
                'pickup_district_id' => 1454,
                'pickup_district_name' => 'Cau Giay',
                'pickup_ward_code' => 'HN-CG-01',
                'pickup_ward_name' => 'Dich Vong Hau',
                'is_active' => 1,
                'is_deleted' => 0,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('shipping_carriers', [
            'code' => 'GHN-WEB',
            'provider' => ShippingCarrier::PROVIDER_GHN,
            'default_weight' => 1400,
            'default_length' => 30,
            'default_width' => 22,
            'default_height' => 12,
            'default_service_type_id' => 5,
            'default_payment_type_id' => 2,
            'default_required_note' => 'CHOXEMHANGKHONGTHU',
            'pickup_ward_code' => 'HN-CG-01',
            'pickup_district_id' => 1454,
        ]);
    }

    public function test_catalog_pages_render_index_create_and_edit_separately(): void
    {
        $admin = $this->seededSuperAdmin();
        $category = Category::query()->create([
            'name' => 'SSR Category',
            'description' => 'Catalog bucket',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $supplier = Supplier::query()->create([
            'supplier_code' => 'SUP-SSR',
            'name' => 'SSR Supplier',
            'phone' => '0900000001',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'PROD-SSR-001',
            'slug' => 'prod-ssr-001',
            'name' => 'SSR Product',
            'sale_price' => 150000,
            'stock_quantity' => 12,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->actingAs($admin, 'web')->get('/admin-web/products')->assertOk()->assertDontSee('name="sku"', false);

        $productCreateResponse = $this->actingAs($admin, 'web')->get('/admin-web/products/create');
        $productCreateResponse->assertOk()->assertSee('Tạo sản phẩm')->assertSee('name="sku"', false);
        $this->assertProductAdminFormHidesRemovedFields($productCreateResponse);

        $productEditResponse = $this->actingAs($admin, 'web')->get("/admin-web/products/{$product->id}/edit");
        $productEditResponse->assertOk()->assertSee('Sửa sản phẩm')->assertSee('SSR Product');
        $this->assertProductAdminFormHidesRemovedFields($productEditResponse);

        $this->actingAs($admin, 'web')->get('/admin-web/categories')->assertOk()->assertDontSee('name="description"', false);
        $this->actingAs($admin, 'web')->get('/admin-web/categories/create')->assertOk()->assertSee('Tạo danh mục')->assertSee('name="description"', false);
        $this->actingAs($admin, 'web')->get("/admin-web/categories/{$category->id}/edit")->assertOk()->assertSee('Sửa danh mục')->assertSee('SSR Category');

        $this->actingAs($admin, 'web')->get('/admin-web/suppliers')->assertOk()->assertDontSee('name="supplier_code"', false);
        $this->actingAs($admin, 'web')->get('/admin-web/suppliers/create')->assertOk()->assertSee('Tạo nhà cung cấp')->assertSee('name="supplier_code"', false);
        $this->actingAs($admin, 'web')->get("/admin-web/suppliers/{$supplier->id}/edit")->assertOk()->assertSee('Sửa nhà cung cấp')->assertSee('SSR Supplier');
    }

    public function test_admin_can_create_product_with_multiple_uploaded_images(): void
    {
        Storage::fake('public');

        $admin = $this->seededSuperAdmin();
        $category = Category::query()->create([
            'name' => 'Upload Category',
            'description' => 'Catalog bucket',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($admin, 'web')
            ->post('/admin-web/products', [
                'category_id' => $category->id,
                'sku' => 'UPLOAD-001',
                'name' => 'Uploaded Product',
                'description' => 'Product with uploaded images',
                'sale_price' => 150000,
                'stock_quantity' => 12,
                'is_active' => 1,
                'is_deleted' => 0,
                'image_url' => $this->fakeUploadedImage('thumbnail.png'),
                'images' => [
                    $this->fakeUploadedImage('front.png'),
                    $this->fakeUploadedImage('detail.png'),
                ],
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin-web.products.index'));

        $product = Product::query()->where('sku', 'UPLOAD-001')->firstOrFail();

        $galleryPaths = explode('|', (string) $product->gallery);
        $this->assertCount(2, $galleryPaths);
        $this->assertStringStartsWith('products/', (string) $product->image_url);

        Storage::disk('public')->assertExists((string) $product->image_url);

        foreach ($galleryPaths as $path) {
            $this->assertStringStartsWith('products/', $path);
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_admin_product_edit_appends_uploaded_images(): void
    {
        Storage::fake('public');

        $admin = $this->seededSuperAdmin();
        $category = Category::query()->create([
            'name' => 'Append Category',
            'description' => 'Catalog bucket',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => 'APPEND-001',
            'name' => 'Append Product',
            'description' => 'Existing product',
            'sale_price' => 150000,
            'stock_quantity' => 12,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $existing = $this->createStoredGalleryImage($product, 'existing.jpg');
        $product->update([
            'image_url' => $existing,
            'gallery' => $existing,
        ]);

        $this->actingAs($admin, 'web')
            ->post("/admin-web/products/{$product->id}", [
                '_method' => 'PUT',
                'category_id' => $category->id,
                'sku' => 'APPEND-001',
                'name' => 'Append Product',
                'description' => 'Existing product',
                'sale_price' => 150000,
                'stock_quantity' => 12,
                'is_active' => 1,
                'is_deleted' => 0,
                'existing_gallery' => [$existing],
                'images' => [
                    $this->fakeUploadedImage('new.png'),
                ],
            ])
            ->assertRedirect(route('admin-web.products.edit', $product->id));

        $product->refresh();
        $galleryPaths = explode('|', (string) $product->gallery);
        $this->assertCount(2, $galleryPaths);
        $this->assertSame($existing, $galleryPaths[0]);
        $this->assertStringStartsWith('products/', $galleryPaths[1]);
        $this->assertSame($existing, $product->image_url);
        Storage::disk('public')->assertExists($galleryPaths[1]);
    }

    public function test_admin_product_edit_can_remove_existing_gallery_images(): void
    {
        Storage::fake('public');

        $admin = $this->seededSuperAdmin();
        $category = Category::query()->create([
            'name' => 'Primary Category',
            'description' => 'Catalog bucket',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => 'PRIMARY-001',
            'name' => 'Primary Product',
            'description' => 'Existing product',
            'sale_price' => 150000,
            'stock_quantity' => 12,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $removed = $this->createStoredGalleryImage($product, 'removed.jpg');
        $kept = $this->createStoredGalleryImage($product, 'kept.jpg');
        $product->update([
            'image_url' => $removed,
            'gallery' => $removed.'|'.$kept,
        ]);

        $this->actingAs($admin, 'web')
            ->post("/admin-web/products/{$product->id}", [
                '_method' => 'PUT',
                'category_id' => $category->id,
                'sku' => 'PRIMARY-001',
                'name' => 'Primary Product',
                'description' => 'Existing product',
                'sale_price' => 150000,
                'stock_quantity' => 12,
                'is_active' => 1,
                'is_deleted' => 0,
                'existing_gallery' => [$kept],
            ])
            ->assertRedirect(route('admin-web.products.edit', $product->id));

        $this->assertSame($kept, $product->refresh()->gallery);
        $this->assertSame($removed, $product->image_url);
        Storage::disk('public')->assertExists($removed);
        Storage::disk('public')->assertExists($kept);
    }

    public function test_user_and_shipping_carrier_pages_render_create_and_edit_separately(): void
    {
        $admin = $this->seededSuperAdmin();
        $customer = User::factory()->create([
            'full_name' => 'SSR Customer Page',
            'role' => User::ROLE_CUSTOMER,
        ]);
        $carrier = ShippingCarrier::query()->create([
            'code' => 'SSR-CARRIER',
            'name' => 'SSR Carrier',
            'provider' => ShippingCarrier::PROVIDER_MANUAL,
            'default_weight' => 1000,
            'default_length' => 20,
            'default_width' => 20,
            'default_height' => 10,
            'default_service_type_id' => 2,
            'default_payment_type_id' => 1,
            'default_required_note' => 'KHONGCHOXEMHANG',
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->actingAs($admin, 'web')->get('/admin-web/users')->assertOk()->assertDontSee('name="full_name"', false);

        $userCreateResponse = $this->actingAs($admin, 'web')->get('/admin-web/users/create');
        $userCreateResponse->assertOk()->assertSee('Tạo khách hàng')->assertSee('name="full_name"', false);
        $this->assertCustomerAdminFormHidesRemovedFields($userCreateResponse);

        $userEditResponse = $this->actingAs($admin, 'web')->get("/admin-web/users/{$customer->id}/edit");
        $userEditResponse->assertOk()->assertSee('Sửa khách hàng')->assertSee('SSR Customer Page');
        $this->assertCustomerAdminFormHidesRemovedFields($userEditResponse);

        $this->actingAs($admin, 'web')->get('/admin-web/shipping-carriers')->assertOk()->assertDontSee('name="code"', false);
        $this->actingAs($admin, 'web')->get('/admin-web/shipping-carriers/create')->assertOk()->assertSee('Tạo đơn vị vận chuyển')->assertSee('name="code"', false);
        $this->actingAs($admin, 'web')->get("/admin-web/shipping-carriers/{$carrier->id}/edit")->assertOk()->assertSee('Sửa đơn vị vận chuyển')->assertSee('SSR Carrier');
    }

    public function test_admin_account_pages_use_dedicated_routes_without_role_fields(): void
    {
        $admin = $this->seededSuperAdmin();

        $this->actingAs($admin, 'web')
            ->get('/admin-web/admins')
            ->assertOk()
            ->assertSee('Tài khoản admin')
            ->assertDontSee('name="admin_role_id"', false)
            ->assertDontSee('name="permissions[]"', false);

        $this->actingAs($admin, 'web')
            ->get('/admin-web/admins/create')
            ->assertOk()
            ->assertSee('Tạo tài khoản admin')
            ->assertDontSee('name="admin_role_id"', false)
            ->assertSee('name="password"', false);

        $this->actingAs($admin, 'web')
            ->get('/admin-web/admins/'.$admin->id.'/edit')
            ->assertOk()
            ->assertSee('Sửa tài khoản admin')
            ->assertSee('Đặt lại mật khẩu')
            ->assertDontSee('name="admin_role_id"', false);
    }

    public function test_posts_and_community_pages_use_dedicated_form_routes(): void
    {
        $admin = $this->seededSuperAdmin();
        $post = Post::query()->create([
            'created_by_user_id' => $admin->id,
            'title' => 'SSR Post',
            'excerpt' => 'SSR excerpt',
            'body' => 'SSR body',
            'status' => Post::STATUS_DRAFT,
        ]);

        $this->actingAs($admin, 'web')->get('/admin-web/posts')->assertOk()->assertDontSee('name="title"', false);
        $this->actingAs($admin, 'web')->get('/admin-web/posts/create')->assertOk()->assertSee('Tạo bài viết')->assertSee('name="title"', false);
        $this->actingAs($admin, 'web')->get("/admin-web/posts/{$post->id}/edit")->assertOk()->assertSee('Sửa bài viết')->assertSee('SSR Post');

        $this->actingAs($admin, 'web')->get('/admin-web/community')->assertOk()->assertDontSee('name="supplier_name"', false);
        $this->actingAs($admin, 'web')->get('/admin-web/community/invitations/create')->assertOk()->assertSee('Tạo lời mời nhà cung cấp')->assertSee('name="supplier_name"', false);
    }

    public function test_validation_errors_stay_on_dedicated_create_pages(): void
    {
        $admin = $this->seededSuperAdmin();

        $this->actingAs($admin, 'web')
            ->from('/admin-web/community/invitations/create')
            ->post('/admin-web/community/invitations', [])
            ->assertRedirect('/admin-web/community/invitations/create')
            ->assertSessionHasErrors(['supplier_name', 'contact_name', 'email']);
    }

    public function test_removed_supplier_and_warehouse_portal_routes_return_not_found(): void
    {
        $admin = $this->seededSuperAdmin();

        $this->actingAs($admin, 'web')->get('/admin-web/supplier/requisitions')->assertNotFound();
        $this->actingAs($admin, 'web')->get('/admin-web/warehouse/inventory')->assertNotFound();
    }

    public function test_product_edit_page_is_available_to_active_admin_without_permissions(): void
    {
        $admin = $this->seededPlainAdmin();
        $category = Category::query()->create([
            'name' => 'Protected Category',
            'description' => null,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'sku' => 'PROTECTED-001',
            'slug' => 'protected-001',
            'name' => 'Protected product',
            'sale_price' => 99000,
            'stock_quantity' => 2,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $this->actingAs($admin, 'web')->get('/admin-web/products')->assertOk();
        $this->actingAs($admin, 'web')->get("/admin-web/products/{$product->id}/edit")->assertOk()->assertSee('Protected product');
    }

    private function seededSuperAdmin(): User
    {
        $this->seed(AdminAccessSeeder::class);

        return User::query()
            ->where('email', config('admin_access.super_admin.email'))
            ->firstOrFail();
    }

    private function seededPlainAdmin(): User
    {
        $this->seed(AdminAccessSeeder::class);

        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => null,
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    private function createGhnCarrier(array $attributes = []): ShippingCarrier
    {
        return ShippingCarrier::query()->create([
            'code' => $attributes['code'] ?? 'GHN',
            'name' => $attributes['name'] ?? 'Giao Hang Nhanh',
            'provider' => ShippingCarrier::PROVIDER_GHN,
            'tracking_url_template' => $attributes['tracking_url_template'] ?? 'https://donhang.ghn.vn/?order_code={code}',
            'default_weight' => $attributes['default_weight'] ?? 1000,
            'default_length' => $attributes['default_length'] ?? 20,
            'default_width' => $attributes['default_width'] ?? 20,
            'default_height' => $attributes['default_height'] ?? 10,
            'default_service_type_id' => $attributes['default_service_type_id'] ?? 2,
            'default_payment_type_id' => $attributes['default_payment_type_id'] ?? 1,
            'default_required_note' => $attributes['default_required_note'] ?? 'KHONGCHOXEMHANG',
            'pickup_name' => $attributes['pickup_name'] ?? 'Kho GHN',
            'pickup_phone' => $attributes['pickup_phone'] ?? '0900000999',
            'pickup_address' => $attributes['pickup_address'] ?? '123 Cau Giay',
            'pickup_ward_code' => $attributes['pickup_ward_code'] ?? 'HN-CG-01',
            'pickup_ward_name' => $attributes['pickup_ward_name'] ?? 'Dich Vong Hau',
            'pickup_district_id' => $attributes['pickup_district_id'] ?? 1454,
            'pickup_district_name' => $attributes['pickup_district_name'] ?? 'Cau Giay',
            'pickup_province_id' => $attributes['pickup_province_id'] ?? 201,
            'pickup_province_name' => $attributes['pickup_province_name'] ?? 'Ha Noi',
            'is_active' => $attributes['is_active'] ?? true,
            'is_deleted' => $attributes['is_deleted'] ?? false,
        ]);
    }

    private function assertCustomerAdminFormHidesRemovedFields(\Illuminate\Testing\TestResponse $response): void
    {
        foreach ([
            'reward_points',
            'reward_tier',
            'next_tier_points',
            'newsletter',
            'sms_alerts',
            'order_email',
            'security_alerts',
        ] as $field) {
            $response->assertDontSee("name=\"{$field}\"", false);
        }
    }

    private function assertProductAdminFormHidesRemovedFields(\Illuminate\Testing\TestResponse $response): void
    {
        foreach ([
            'short_description',
            'origin',
            'weight',
            'shelf_life',
            'certifications[]',
            'gallery[]',
        ] as $field) {
            $response->assertDontSee("name=\"{$field}\"", false);
        }
    }

    private function createStoredGalleryImage(Product $product, string $fileName): string
    {
        $path = "products/{$product->id}/{$fileName}";
        Storage::disk('public')->put($path, 'test-image');

        return $path;
    }

    private function fakeUploadedImage(string $fileName): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        );

        return UploadedFile::fake()
            ->createWithContent($fileName, $png)
            ->mimeType('image/png');
    }

    /**
     * @param  array<string, mixed>  $orderAttributes
     * @param  array<string, mixed>  $paymentAttributes
     */
    private function createLogisticsOrder(User $customer, array $orderAttributes = [], array $paymentAttributes = []): Order
    {
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_no' => $orderAttributes['order_no'] ?? 'ORD-SSR-'.strtoupper(substr(uniqid(), -6)),
            'recipient_name' => $orderAttributes['recipient_name'] ?? $customer->full_name,
            'recipient_phone' => $orderAttributes['recipient_phone'] ?? $customer->phone,
            'shipping_address' => $orderAttributes['shipping_address'] ?? '12 Test Street',
            'shipping_line1' => $orderAttributes['shipping_line1'] ?? null,
            'shipping_province_id' => $orderAttributes['shipping_province_id'] ?? null,
            'shipping_province_name' => $orderAttributes['shipping_province_name'] ?? null,
            'shipping_district_id' => $orderAttributes['shipping_district_id'] ?? null,
            'shipping_district_name' => $orderAttributes['shipping_district_name'] ?? null,
            'shipping_ward_code' => $orderAttributes['shipping_ward_code'] ?? null,
            'shipping_ward_name' => $orderAttributes['shipping_ward_name'] ?? null,
            'payment_method' => $orderAttributes['payment_method'] ?? Order::PAYMENT_METHOD_COD,
            'status' => $orderAttributes['status'] ?? Order::STATUS_PENDING,
            'subtotal' => $orderAttributes['subtotal'] ?? 100000,
            'shipping_fee' => $orderAttributes['shipping_fee'] ?? 15000,
            'discount_amount' => $orderAttributes['discount_amount'] ?? 0,
            'total_amount' => $orderAttributes['total_amount'] ?? 115000,
            'stock_deducted' => $orderAttributes['stock_deducted'] ?? false,
            'shipping_code' => $orderAttributes['shipping_code'] ?? null,
            'delivered_at' => $orderAttributes['delivered_at'] ?? null,
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'transaction_code' => $paymentAttributes['transaction_code'] ?? null,
            'payment_method' => $paymentAttributes['payment_method'] ?? $order->payment_method,
            'payment_status' => $paymentAttributes['payment_status'] ?? Payment::STATUS_PENDING,
            'amount' => number_format((float) ($paymentAttributes['amount'] ?? $order->total_amount), 2, '.', ''),
            'gateway_name' => $paymentAttributes['gateway_name'] ?? null,
            'gateway_reference' => $paymentAttributes['gateway_reference'] ?? null,
            'paid_at' => $paymentAttributes['paid_at'] ?? null,
            'raw_payload' => $paymentAttributes['raw_payload'] ?? null,
        ]);

        return $order;
    }
}
