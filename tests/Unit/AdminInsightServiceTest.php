<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Payment;
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
}
