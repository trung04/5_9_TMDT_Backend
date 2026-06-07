<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AdminInsightService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInsightServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_payload_filters_range_revenue_by_datetime_boundaries(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->createDeliveredOrder($customer, 'ORD-IN-001', Carbon::parse('2026-06-01 08:45:00'), 111111);
        $this->createDeliveredOrder($customer, 'ORD-IN-002', Carbon::parse('2026-06-01 09:00:30'), 333333);
        $this->createDeliveredOrder($customer, 'ORD-OUT-001', Carbon::parse('2026-06-01 09:01:00'), 222222);

        $payload = app(AdminInsightService::class)->dashboardPayload([
            'date_from' => '2026-06-01T08:30',
            'date_to' => '2026-06-01T09:00',
            'chart_range' => 'custom',
        ]);

        $this->assertSame(444444.0, $payload['metrics']['range_revenue']);
        $this->assertSame('2026-06-01', $payload['filters']['date_from']);
        $this->assertSame('2026-06-01', $payload['filters']['date_to']);
        $this->assertSame('2026-06-01T08:30', $payload['filters']['date_from_input']);
        $this->assertSame('2026-06-01T09:00', $payload['filters']['date_to_input']);
        $this->assertSame('01/06/2026 08:30 - 01/06/2026 09:00', $payload['filters']['date_range_label']);
        $this->assertSame('Tùy chỉnh', $payload['filters']['chart_range_label']);
        $this->assertSame('01/06/2026 08:30 - 01/06/2026 09:00', $payload['filters']['chart_period_label']);
    }

    public function test_dashboard_payload_exposes_chart_data(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);
        $category = Category::query()->create([
            'name' => 'Chart Category',
            'description' => 'Dashboard chart category',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $supplier = Supplier::query()->create([
            'supplier_code' => 'CHART-SUP',
            'name' => 'Chart Supplier',
            'phone' => '0900000001',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'CHART-PRODUCT',
            'name' => 'Chart Product',
            'description' => 'Product used for dashboard charts',
            'sale_price' => 100000,
            'stock_quantity' => 20,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $deliveredOrder = $this->createDeliveredOrder($customer, 'ORD-CHART-DELIVERED', Carbon::parse('2026-06-02 10:00:00'), 300000);
        OrderItem::query()->create([
            'order_id' => $deliveredOrder->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'quantity' => 3,
            'unit_price' => '100000.00',
            'line_total' => '300000.00',
        ]);
        $this->createOrderWithStatus($customer, 'ORD-CHART-PENDING', Order::STATUS_PENDING);
        $this->createOrderWithStatus($customer, 'ORD-CHART-SHIPPED', Order::STATUS_SHIPPED);

        $payload = app(AdminInsightService::class)->dashboardPayload([
            'date_from' => '2026-06-01',
            'date_to' => '2026-06-03',
            'chart_range' => 'custom',
        ]);

        $revenueByPeriod = collect($payload['revenue_chart'])->keyBy('period');
        $this->assertSame(300000.0, $revenueByPeriod['2026-06-02']['revenue']);
        $this->assertSame(1, $revenueByPeriod['2026-06-02']['successful_orders']);

        $statusChart = collect($payload['order_status_chart'])->keyBy('status');
        $this->assertSame(Order::allowedStatuses(), $statusChart->keys()->all());
        $this->assertSame(1, $statusChart[Order::STATUS_DELIVERED]['count']);
        $this->assertSame(1, $statusChart[Order::STATUS_PENDING]['count']);
        $this->assertSame(1, $statusChart[Order::STATUS_SHIPPED]['count']);

        $this->assertSame('Chart Product', $payload['featured_products'][0]['name']);
        $this->assertSame(3, $payload['featured_products'][0]['sold_quantity']);
        $this->assertSame(300000.0, $payload['featured_products'][0]['revenue']);
    }

    public function test_dashboard_payload_filters_operational_widgets_by_created_at_and_sales_by_delivered_at(): void
    {
        $revenueCustomer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'email' => 'range-revenue@example.com',
        ]);
        $operationsCustomer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'email' => 'range-operations@example.com',
        ]);
        $category = Category::query()->create([
            'name' => 'Range Category',
            'description' => 'Dashboard range category',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $supplier = Supplier::query()->create([
            'supplier_code' => 'RANGE-SUP',
            'name' => 'Range Supplier',
            'phone' => '0900000002',
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $revenueProduct = Product::query()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'RANGE-REVENUE-PRODUCT',
            'name' => 'Range Revenue Product',
            'sale_price' => 500000,
            'stock_quantity' => 20,
            'is_active' => true,
            'is_deleted' => false,
        ]);
        $outsideProduct = Product::query()->create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'RANGE-OUTSIDE-PRODUCT',
            'name' => 'Range Outside Product',
            'sale_price' => 700000,
            'stock_quantity' => 20,
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $revenueInCreatedOut = $this->createDeliveredOrder(
            $revenueCustomer,
            'ORD-RANGE-REVENUE-IN',
            Carbon::parse('2026-06-02 09:00:00'),
            500000,
            Carbon::parse('2026-05-25 09:00:00'),
        );
        OrderItem::query()->create([
            'order_id' => $revenueInCreatedOut->id,
            'product_id' => $revenueProduct->id,
            'product_name_snapshot' => $revenueProduct->name,
            'quantity' => 1,
            'unit_price' => '500000.00',
            'line_total' => '500000.00',
        ]);

        $revenueOutCreatedIn = $this->createDeliveredOrder(
            $operationsCustomer,
            'ORD-RANGE-REVENUE-OUT',
            Carbon::parse('2026-05-25 09:00:00'),
            700000,
            Carbon::parse('2026-06-02 09:00:00'),
        );
        OrderItem::query()->create([
            'order_id' => $revenueOutCreatedIn->id,
            'product_id' => $outsideProduct->id,
            'product_name_snapshot' => $outsideProduct->name,
            'quantity' => 1,
            'unit_price' => '700000.00',
            'line_total' => '700000.00',
        ]);

        $this->createOrderWithStatus(
            $operationsCustomer,
            'ORD-RANGE-PENDING-IN',
            Order::STATUS_PENDING,
            Carbon::parse('2026-06-02 10:00:00'),
        );
        $this->createOrderWithStatus(
            $operationsCustomer,
            'ORD-RANGE-PENDING-OUT',
            Order::STATUS_PENDING,
            Carbon::parse('2026-05-25 10:00:00'),
        );

        $payload = app(AdminInsightService::class)->dashboardPayload([
            'date_from' => '2026-06-01T00:00',
            'date_to' => '2026-06-03T23:59',
            'chart_range' => 'custom',
        ]);

        $this->assertSame(500000.0, $payload['metrics']['revenue']);
        $this->assertSame(500000.0, $payload['metrics']['range_revenue']);
        $this->assertSame(1, $payload['metrics']['successful_orders']);

        $statusChart = collect($payload['order_status_chart'])->keyBy('status');
        $this->assertSame(1, $statusChart[Order::STATUS_PENDING]['count']);
        $this->assertSame(1, $statusChart[Order::STATUS_DELIVERED]['count']);

        $recentOrderNos = collect($payload['recent_orders'])->pluck('order_no');
        $this->assertTrue($recentOrderNos->contains('ORD-RANGE-PENDING-IN'));
        $this->assertTrue($recentOrderNos->contains('ORD-RANGE-REVENUE-OUT'));
        $this->assertFalse($recentOrderNos->contains('ORD-RANGE-PENDING-OUT'));
        $this->assertFalse($recentOrderNos->contains('ORD-RANGE-REVENUE-IN'));

        $featuredProductNames = collect($payload['featured_products'])->pluck('name');
        $this->assertTrue($featuredProductNames->contains('Range Revenue Product'));
        $this->assertFalse($featuredProductNames->contains('Range Outside Product'));

        $topCustomerEmails = collect($payload['top_customers'])->pluck('email');
        $this->assertTrue($topCustomerEmails->contains('range-revenue@example.com'));
        $this->assertFalse($topCustomerEmails->contains('range-operations@example.com'));
    }

    private function createDeliveredOrder(User $customer, string $orderNo, Carbon $deliveredAt, int $totalAmount, ?Carbon $createdAt = null): Order
    {
        $createdAt ??= $deliveredAt->copy();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_no' => $orderNo,
            'recipient_name' => $customer->full_name,
            'recipient_phone' => $customer->phone,
            'shipping_address' => '12 Dashboard Range Street',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => Order::STATUS_DELIVERED,
            'subtotal' => $totalAmount,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => $totalAmount,
            'stock_deducted' => true,
            'delivered_at' => $deliveredAt,
        ]);
        $order->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $deliveredAt,
        ])->save();

        Payment::query()->create([
            'order_id' => $order->id,
            'transaction_code' => null,
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'payment_status' => Payment::STATUS_SUCCESS,
            'amount' => number_format((float) $totalAmount, 2, '.', ''),
            'gateway_name' => null,
            'gateway_reference' => null,
            'paid_at' => $deliveredAt,
            'raw_payload' => null,
        ]);

        return $order;
    }

    private function createOrderWithStatus(User $customer, string $orderNo, string $status, ?Carbon $createdAt = null): Order
    {
        $createdAt ??= Carbon::parse('2026-06-02 12:00:00');

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'order_no' => $orderNo,
            'recipient_name' => $customer->full_name,
            'recipient_phone' => $customer->phone,
            'shipping_address' => '12 Dashboard Status Street',
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'status' => $status,
            'subtotal' => 100000,
            'shipping_fee' => 0,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'stock_deducted' => true,
        ]);
        $order->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $order;
    }
}
