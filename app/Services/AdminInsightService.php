<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AdminInsightService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function dashboardPayload(array $filters = [], ?User $actor = null): array
    {
        $suppliers = Supplier::query()
            ->available()
            ->latest('id')
            ->get();
        $complaintCount = Complaint::query()->count();
        $lowStockThreshold = $this->resolveLowStockThreshold($actor);

        [$rangeStart, $rangeEnd] = $this->resolveDateRange($filters);
        [$chartStart, $chartEnd, $chartRange] = $this->resolveChartRange($filters, $rangeStart, $rangeEnd);

        $successfulOrdersQuery = $this->applyDeliveredBetween(
            $this->successfulRevenueOrdersQuery(),
            $rangeStart,
            $rangeEnd,
        );
        $totalRevenue = (float) (clone $successfulOrdersQuery)->sum('total_amount');
        $successfulOrdersCount = (int) (clone $successfulOrdersQuery)->count();
        $averageOrderValue = $successfulOrdersCount > 0
            ? round($totalRevenue / $successfulOrdersCount, 2)
            : 0.0;

        $todayRevenue = (float) $this->applyDeliveredBetween(
            $this->successfulRevenueOrdersQuery(),
            now()->copy()->startOfDay(),
            now()->copy()->endOfDay(),
        )->sum('total_amount');
        $monthlyRevenue = (float) $this->applyDeliveredBetween(
            $this->successfulRevenueOrdersQuery(),
            now()->copy()->startOfMonth(),
            now()->copy()->endOfMonth(),
        )->sum('total_amount');
        $rangeRevenue = $totalRevenue;

        $processingOrdersCount = $this->applyCreatedBetween(Order::query(), $rangeStart, $rangeEnd)->whereIn('status', [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PACKED,
            Order::STATUS_SHIPPED,
        ])->count();
        $pendingOrdersCount = $this->applyCreatedBetween(Order::query(), $rangeStart, $rangeEnd)
            ->where('status', Order::STATUS_PENDING)
            ->count();
        $shippingOrdersCount = $this->applyCreatedBetween(Order::query(), $rangeStart, $rangeEnd)
            ->where('status', Order::STATUS_SHIPPED)
            ->count();
        $deliveryFailedOrdersCount = $this->applyCreatedBetween(Order::query(), $rangeStart, $rangeEnd)
            ->where('status', Order::STATUS_DELIVERY_FAILED)
            ->count();
        $orderStatusCounts = $this->applyCreatedBetween(
            Order::query()
                ->select('status')
                ->selectRaw('COUNT(*) as orders_count'),
            $rangeStart,
            $rangeEnd,
        )
            ->groupBy('status')
            ->pluck('orders_count', 'status');
        $orderStatusChart = collect(Order::allowedStatuses())
            ->map(fn (string $status): array => [
                'status' => $status,
                'label' => $this->orderStatusLabel($status),
                'count' => (int) ($orderStatusCounts[$status] ?? 0),
            ])
            ->all();

        $bankTransferPendingBase = $this->applyCreatedBetween(Order::query(), $rangeStart, $rangeEnd)
            ->where('payment_method', Order::PAYMENT_METHOD_BANK_TRANSFER)
            ->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_DELIVERED])
            ->whereHas('payment', function (Builder $paymentQuery): void {
                $paymentQuery->where('payment_status', Payment::STATUS_PENDING);
            });
        $bankTransferPendingCount = (clone $bankTransferPendingBase)->count();
        $customerReportedTransferCount = (clone $bankTransferPendingBase)
            ->whereHas('payment', function (Builder $paymentQuery): void {
                $paymentQuery
                    ->where('payment_status', Payment::STATUS_PENDING)
                    ->where('raw_payload->customer_transfer_submitted', true);
            })
            ->count();

        $lowStockProductsQuery = Product::query()
            ->available()
            ->where('stock_quantity', '<=', $lowStockThreshold)
            ->orderBy('stock_quantity')
            ->orderBy('id');
        $lowStockProductsCount = (clone $lowStockProductsQuery)->count();
        $lowStockProducts = (clone $lowStockProductsQuery)
            ->limit(5)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock_quantity' => (int) $product->stock_quantity,
                'sale_price' => (float) $product->sale_price,
            ])
            ->all();

        $bestSellingProducts = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as sold_quantity, SUM(line_total) as revenue_amount')
            ->whereHas('order', function (Builder $query) use ($rangeStart, $rangeEnd): void {
                $this->applySuccessfulRevenueConstraints($query);
                $this->applyDeliveredBetween($query, $rangeStart, $rangeEnd);
            })
            ->groupBy('product_id')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('revenue_amount')
            ->limit(5)
            ->with('product')
            ->get()
            ->map(function (OrderItem $item): array {
                $product = $item->product;

                return [
                    'id' => $product?->id,
                    'name' => $product?->name ?? 'San pham khong con ton tai',
                    'sku' => $product?->sku ?? 'N/A',
                    'description' => $product?->description,
                    'sale_price' => $product?->sale_price ?? 0,
                    'stock_quantity' => $product?->stock_quantity ?? 0,
                    'sold_quantity' => (int) ($item->sold_quantity ?? 0),
                    'revenue' => (float) ($item->revenue_amount ?? 0),
                ];
            })
            ->all();

        $topCustomers = User::query()
            ->select('users.id', 'users.full_name', 'users.email')
            ->selectRaw('COUNT(DISTINCT orders.id) as successful_orders')
            ->selectRaw('SUM(orders.total_amount) as total_revenue')
            ->selectRaw('MAX(orders.delivered_at) as last_delivered_at')
            ->join('orders', 'orders.user_id', '=', 'users.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', Order::STATUS_DELIVERED)
            ->where('payments.payment_status', Payment::STATUS_SUCCESS)
            ->whereBetween('orders.delivered_at', [$rangeStart->copy(), $rangeEnd->copy()])
            ->groupBy('users.id', 'users.full_name', 'users.email')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn (User $customer): array => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'successful_orders' => (int) ($customer->successful_orders ?? 0),
                'total_revenue' => (float) ($customer->total_revenue ?? 0),
                'last_delivered_at' => $customer->last_delivered_at
                    ? Carbon::parse((string) $customer->last_delivered_at)->toISOString()
                    : null,
            ])
            ->all();
        
        $recentOrders = $this->applyCreatedBetween(Order::query(), $rangeStart, $rangeEnd)
            ->with(['payment', 'user'])
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Order $order): array => $this->orderSnapshot($order))
            ->all();

        $workQueue = [
            [
                'key' => 'pending_orders',
                'label' => 'Đơn mới chờ xác nhận',
                'count' => $pendingOrdersCount,
                'orders' => $this->taskOrders($rangeStart, $rangeEnd, fn (Builder $query) => $query->where('status', Order::STATUS_PENDING)),
            ],
            [
                'key' => 'bank_transfer_pending',
                'label' => 'Đơn chuyển khoản chờ xác nhận',
                'count' => $bankTransferPendingCount,
                'orders' => $this->taskOrders($rangeStart, $rangeEnd, function (Builder $query): void {
                    $query->where('payment_method', Order::PAYMENT_METHOD_BANK_TRANSFER)
                        ->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_DELIVERED])
                        ->whereHas('payment', function (Builder $paymentQuery): void {
                            $paymentQuery->where('payment_status', Payment::STATUS_PENDING);
                        });
                }),
            ],
            [
                'key' => 'confirmed_orders',
                'label' => 'Đơn đã xác nhận cần đóng gói',
                'count' => $this->applyCreatedBetween(Order::query(), $rangeStart, $rangeEnd)
                    ->where('status', Order::STATUS_CONFIRMED)
                    ->count(),
                'orders' => $this->taskOrders($rangeStart, $rangeEnd, fn (Builder $query) => $query->where('status', Order::STATUS_CONFIRMED)),
            ],
            [
                'key' => 'packed_orders',
                'label' => 'Đơn đã đóng gói cần giao',
                'count' => $this->applyCreatedBetween(Order::query(), $rangeStart, $rangeEnd)
                    ->where('status', Order::STATUS_PACKED)
                    ->count(),
                'orders' => $this->taskOrders($rangeStart, $rangeEnd, fn (Builder $query) => $query->where('status', Order::STATUS_PACKED)),
            ],
            [
                'key' => 'shipped_orders',
                'label' => 'Đơn đang giao',
                'count' => $shippingOrdersCount,
                'orders' => $this->taskOrders($rangeStart, $rangeEnd, fn (Builder $query) => $query->where('status', Order::STATUS_SHIPPED)),
            ],
            [
                'key' => 'delivery_failed_orders',
                'label' => 'Đơn giao thất bại cần xử lý',
                'count' => $deliveryFailedOrdersCount,
                'orders' => $this->taskOrders($rangeStart, $rangeEnd, fn (Builder $query) => $query->where('status', Order::STATUS_DELIVERY_FAILED)),
            ],
        ];

        $revenueChartRows = $this->applyDeliveredBetween(
            Order::query()
                ->selectRaw('DATE(delivered_at) as period')
                ->selectRaw('SUM(total_amount) as revenue')
                ->selectRaw('COUNT(*) as successful_orders')
                ->whereNotNull('delivered_at')
                ->tap(fn (Builder $query) => $this->applySuccessfulRevenueConstraints($query)),
            $chartStart,
            $chartEnd,
        )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $revenueChart = [];
        $cursor = $chartStart->copy()->startOfDay();

        while ($cursor->lte($chartEnd)) {
            $period = $cursor->toDateString();
            $row = $revenueChartRows->get($period);

            $revenueChart[] = [
                'period' => $period,
                'label' => $cursor->format('d/m'),
                'revenue' => (float) ($row->revenue ?? 0),
                'successful_orders' => (int) ($row->successful_orders ?? 0),
            ];

            $cursor->addDay();
        }

        return [
            'metrics' => [
                'revenue' => $totalRevenue,
                'today_revenue' => $todayRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'range_revenue' => $rangeRevenue,
                'successful_orders' => $successfulOrdersCount,
                'processing_orders' => $processingOrdersCount,
                'pending_orders' => $pendingOrdersCount,
                'bank_transfer_pending' => $bankTransferPendingCount,
                'customer_reported_transfer' => $customerReportedTransferCount,
                'shipping_orders' => $shippingOrdersCount,
                'delivery_failed_orders' => $deliveryFailedOrdersCount,
                'low_stock_products' => $lowStockProductsCount,
                'low_stock_threshold' => $lowStockThreshold,
                'supplier_count' => $suppliers->count(),
                'product_count' => Product::query()
                    ->available()
                    ->count(),
                'average_order_value' => $averageOrderValue,
                'complaint_count' => $complaintCount,
            ],
            'filters' => [
                'date_from' => $rangeStart->toDateString(),
                'date_to' => $rangeEnd->toDateString(),
                'date_from_input' => $this->formatDateTimeLocal($rangeStart),
                'date_to_input' => $this->formatDateTimeLocal($rangeEnd),
                'chart_range' => $chartRange,
                'date_range_label' => $this->formatDateRangeLabel($rangeStart, $rangeEnd),
                'chart_range_label' => $this->chartRangeLabel($chartRange),
                'chart_period_label' => $this->formatDateRangeLabel($chartStart, $chartEnd),
            ],
            'recent_orders' => $recentOrders,
            'work_queue' => $workQueue,
            'featured_suppliers' => $suppliers->take(5)->map(fn (Supplier $supplier): array => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_name' => $supplier->contact_name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
            ])->all(),
            'featured_products' => $bestSellingProducts,
            'low_stock_products' => $lowStockProducts,
            'top_customers' => $topCustomers,
            'revenue_chart' => $revenueChart,
            'order_status_chart' => $orderStatusChart,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function communityPayload(): array
    {
        $suppliers = Supplier::query()
            ->available()
            ->withCount('products')
            ->get();
        $customers = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->withCount([
                'orders as successful_orders_count' => function (Builder $query): void {
                    $this->applySuccessfulRevenueConstraints($query);
                },
            ])
            ->withSum([
                'orders as successful_orders_sum_total_amount' => function (Builder $query): void {
                    $this->applySuccessfulRevenueConstraints($query);
                },
            ], 'total_amount')
            ->latest('id')
            ->get();
        $invitations = SupplierInvitation::query()->latest('id')->get();

        return [
            'suppliers' => $suppliers->map(fn (Supplier $supplier): array => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_name' => $supplier->contact_name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'product_count' => $supplier->products_count ?? 0,
                'is_active' => (bool) $supplier->is_active,
                'is_deleted' => (bool) $supplier->is_deleted,
            ])->all(),
            'customers' => $customers->map(fn (User $customer): array => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'order_count' => $customer->successful_orders_count ?? 0,
                'total_spend' => $customer->successful_orders_sum_total_amount ?? 0,
                'is_active' => (bool) $customer->is_active,
                'is_deleted' => (bool) $customer->is_deleted,
            ])->all(),
            'invitations' => $invitations->map(fn (SupplierInvitation $invitation): array => [
                'id' => $invitation->id,
                'supplier_name' => $invitation->supplier_name,
                'contact_name' => $invitation->contact_name,
                'email' => $invitation->email,
                'categories' => $invitation->categories ?? [],
                'note' => $invitation->note,
                'status' => $invitation->status,
                'created_at' => optional($invitation->created_at)->toISOString(),
            ])->all(),
        ];
    }

    public function createSupplierInvitation(array $attributes, User $actor): SupplierInvitation
    {
        return SupplierInvitation::query()->create([
            'supplier_name' => $attributes['supplier_name'],
            'contact_name' => $attributes['contact_name'],
            'email' => $attributes['email'],
            'categories' => $attributes['categories'] ?? [],
            'note' => $attributes['note'] ?? null,
            'status' => SupplierInvitation::STATUS_SENT,
            'created_by_user_id' => $actor->id,
        ]);
    }

    private function successfulRevenueOrdersQuery(): Builder
    {
        $query = Order::query();
        $this->applySuccessfulRevenueConstraints($query);

        return $query;
    }

    private function applySuccessfulRevenueConstraints(Builder $query): Builder
    {
        return $query
            ->where('status', Order::STATUS_DELIVERED)
            ->whereHas('payment', function (Builder $paymentQuery): void {
                $paymentQuery->where('payment_status', Payment::STATUS_SUCCESS);
            });
    }

    private function applyDeliveredBetween(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('delivered_at', [$start->copy(), $end->copy()]);
    }

    private function applyCreatedBetween(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('created_at', [$start->copy(), $end->copy()]);
    }

    /**
     * @param  callable(Builder): void  $scope
     * @return list<array<string, mixed>>
     */
    private function taskOrders(Carbon $start, Carbon $end, callable $scope): array
    {
        $query = $this->applyCreatedBetween(
            Order::query()->with(['payment', 'user']),
            $start,
            $end,
        )->latest('id')->limit(5);
        $scope($query);

        return $query->get()->map(fn (Order $order): array => $this->orderSnapshot($order))->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function orderSnapshot(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment?->payment_status,
            'total_amount' => $order->total_amount,
            'created_at' => optional($order->created_at)->toISOString(),
            'customer' => $order->user ? [
                'id' => $order->user->id,
                'full_name' => $order->user->full_name,
                'email' => $order->user->email,
            ] : null,
        ];
    }

    private function resolveLowStockThreshold(?User $actor): int
    {
        if ($actor) {
            $threshold = AdminSetting::query()
                ->where('user_id', $actor->id)
                ->value('low_stock_threshold');

            if (is_numeric($threshold)) {
                return max(1, (int) $threshold);
            }
        }

        $fallback = AdminSetting::query()->latest('id')->value('low_stock_threshold');

        return is_numeric($fallback) ? max(1, (int) $fallback) : 5;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(array $filters): array
    {
        $start = $this->parseDateBoundary($filters['date_from'] ?? null, true)
            ?? now()->copy()->startOfMonth();
        $end = $this->parseDateBoundary($filters['date_to'] ?? null, false)
            ?? now()->copy()->endOfMonth();

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveChartRange(array $filters, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $chartRange = isset($filters['chart_range']) && is_string($filters['chart_range']) && $filters['chart_range'] !== ''
            ? $filters['chart_range']
            : '30d';

        return match ($chartRange) {
            '7d' => [now()->copy()->subDays(6)->startOfDay(), now()->copy()->endOfDay(), '7d'],
            'this_month' => [now()->copy()->startOfMonth(), now()->copy()->endOfDay(), 'this_month'],
            'custom' => [$rangeStart->copy(), $rangeEnd->copy(), 'custom'],
            default => [now()->copy()->subDays(29)->startOfDay(), now()->copy()->endOfDay(), '30d'],
        };
    }

    private function parseDateBoundary(mixed $value, bool $isStart): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $normalized = trim($value);
        $date = Carbon::parse($normalized);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized) === 1) {
            return $isStart ? $date->startOfDay() : $date->endOfDay();
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}$/', $normalized) === 1) {
            return $isStart ? $date->startOfMinute() : $date->endOfMinute();
        }

        return $date;
    }

    private function formatDateTimeLocal(Carbon $value): string
    {
        return $value->format('Y-m-d\TH:i');
    }

    private function formatDateRangeLabel(Carbon $start, Carbon $end): string
    {
        return $this->formatDateTimeDisplay($start).' - '.$this->formatDateTimeDisplay($end);
    }

    private function formatDateTimeDisplay(Carbon $value): string
    {
        return $value->format('d/m/Y H:i');
    }

    private function chartRangeLabel(string $chartRange): string
    {
        return match ($chartRange) {
            '7d' => '7 ngày gần nhất',
            'this_month' => 'Tháng này',
            'custom' => 'Tùy chỉnh',
            default => '30 ngày gần nhất',
        };
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            Order::STATUS_PENDING => 'Cho xac nhan',
            Order::STATUS_CONFIRMED => 'Da xac nhan',
            Order::STATUS_PACKED => 'Da dong goi',
            Order::STATUS_SHIPPED => 'Dang giao',
            Order::STATUS_DELIVERED => 'Da giao',
            Order::STATUS_DELIVERY_FAILED => 'Giao that bai',
            Order::STATUS_CANCELLED => 'Da huy',
            default => $status,
        };
    }
}
