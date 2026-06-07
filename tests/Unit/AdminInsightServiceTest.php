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

    private function createDeliveredOrder(User $customer, string $orderNo, Carbon $deliveredAt, int $totalAmount): Order
    {
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

    private function createOrderWithStatus(User $customer, string $orderNo, string $status): Order
    {
        return Order::query()->create([
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
    }
}
