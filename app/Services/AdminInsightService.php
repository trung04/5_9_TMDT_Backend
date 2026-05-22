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

        $successfulOrdersQuery = $this->successfulRevenueOrdersQuery();
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
        $rangeRevenue = (float) $this->applyDeliveredBetween(
            $this->successfulRevenueOrdersQuery(),
            $rangeStart,
            $rangeEnd,
        )->sum('total_amount');

        $processingOrdersCount = Order::query()->whereIn('status', [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PACKED,
            Order::STATUS_SHIPPED,
        ])->count();
        $pendingOrdersCount = Order::query()->where('status', Order::STATUS_PENDING)->count();
        $shippingOrdersCount = Order::query()->where('status', Order::STATUS_SHIPPED)->count();
        $deliveryFailedOrdersCount = Order::query()->where('status', Order::STATUS_DELIVERY_FAILED)->count();

        $bankTransferPendingBase = Order::query()
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
            ->whereHas('order', function (Builder $query): void {
                $this->applySuccessfulRevenueConstraints($query);
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

        $recentOrders = Order::query()
            ->with(['payment', 'user'])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (Order $order): array => $this->orderSnapshot($order))
            ->all();

        $workQueue = [
            [
                'key' => 'pending_orders',
                'label' => 'Đơn mới chờ xác nhận',
                'count' => $pendingOrdersCount,
                'orders' => $this->taskOrders(fn (Builder $query) => $query->where('status', Order::STATUS_PENDING)),
            ],
            [
                'key' => 'bank_transfer_pending',
                'label' => 'Đơn chuyển khoản chờ xác nhận',
                'count' => $bankTransferPendingCount,
                'orders' => $this->taskOrders(function (Builder $query): void {
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
                'count' => Order::query()->where('status', Order::STATUS_CONFIRMED)->count(),
                'orders' => $this->taskOrders(fn (Builder $query) => $query->where('status', Order::STATUS_CONFIRMED)),
            ],
            [
                'key' => 'packed_orders',
                'label' => 'Đơn đã đóng gói cần giao',
                'count' => Order::query()->where('status', Order::STATUS_PACKED)->count(),
                'orders' => $this->taskOrders(fn (Builder $query) => $query->where('status', Order::STATUS_PACKED)),
            ],
            [
                'key' => 'shipped_orders',
                'label' => 'Đơn đang giao',
                'count' => $shippingOrdersCount,
                'orders' => $this->taskOrders(fn (Builder $query) => $query->where('status', Order::STATUS_SHIPPED)),
            ],
            [
                'key' => 'delivery_failed_orders',
                'label' => 'Đơn giao thất bại cần xử lý',
                'count' => $deliveryFailedOrdersCount,
                'orders' => $this->taskOrders(fn (Builder $query) => $query->where('status', Order::STATUS_DELIVERY_FAILED)),
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
                'chart_range' => $chartRange,
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
        return $query->whereBetween('delivered_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
    }

    /**
     * @param  callable(Builder): void  $scope
     * @return list<array<string, mixed>>
     */
    private function taskOrders(callable $scope): array
    {
        $query = Order::query()->with(['payment', 'user'])->latest('id')->limit(5);
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
        $start = isset($filters['date_from']) && is_string($filters['date_from']) && $filters['date_from'] !== ''
            ? Carbon::parse($filters['date_from'])
            : now()->copy()->startOfMonth();
        $end = isset($filters['date_to']) && is_string($filters['date_to']) && $filters['date_to'] !== ''
            ? Carbon::parse($filters['date_to'])
            : now()->copy()->endOfMonth();

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start->startOfDay(), $end->endOfDay()];
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
            'custom' => [$rangeStart->copy()->startOfDay(), $rangeEnd->copy()->endOfDay(), 'custom'],
            default => [now()->copy()->subDays(29)->startOfDay(), now()->copy()->endOfDay(), '30d'],
        };
    }
}
