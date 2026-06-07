@extends('admin-web.layouts.app')

@section('title', 'Bảng điều khiển')

@section('content')
    @php
        $labels = \App\Support\AdminWebLabel::class;
        $canViewOrderDetails = $adminUser->hasAdminPermission('admin.orders.view');
        $revenueChart = collect($payload['revenue_chart'] ?? [])->values();
        $orderStatusChart = collect($payload['order_status_chart'] ?? [])->values();
        $topProductChart = collect($payload['featured_products'] ?? [])
            ->filter(fn (array $product): bool => (int) ($product['sold_quantity'] ?? 0) > 0)
            ->values();
        $hasRevenueChartData = $revenueChart->sum('revenue') > 0 || $revenueChart->sum('successful_orders') > 0;
        $hasOrderStatusChartData = $orderStatusChart->sum('count') > 0;
        $hasTopProductChartData = $topProductChart->isNotEmpty();
        $dashboardChartData = [
            'revenue' => [
                'labels' => $revenueChart->pluck('label')->all(),
                'revenue' => $revenueChart->pluck('revenue')->map(fn ($value): float => (float) $value)->all(),
                'orders' => $revenueChart->pluck('successful_orders')->map(fn ($value): int => (int) $value)->all(),
            ],
            'orderStatus' => [
                'labels' => $orderStatusChart->pluck('label')->all(),
                'counts' => $orderStatusChart->pluck('count')->map(fn ($value): int => (int) $value)->all(),
            ],
            'topProducts' => [
                'labels' => $topProductChart->pluck('name')->map(fn ($value): string => \Illuminate\Support\Str::limit((string) $value, 26))->all(),
                'sold' => $topProductChart->pluck('sold_quantity')->map(fn ($value): int => (int) $value)->all(),
                'revenue' => $topProductChart->pluck('revenue')->map(fn ($value): float => (float) $value)->all(),
            ],
        ];
    @endphp

    <div class="toolbar">
        <div>
            <h2>Bảng điều khiển</h2>
        </div>
        <form class="filters card" method="GET" action="{{ route('admin-web.dashboard') }}" style="margin: 0;">
            <label>
                Từ ngày
                <input type="datetime-local" name="date_from" step="60" value="{{ $payload['filters']['date_from_input'] ?? '' }}">
            </label>
            <label>
                Đến ngày
                <input type="datetime-local" name="date_to" step="60" value="{{ $payload['filters']['date_to_input'] ?? '' }}">
            </label>
            <label>
                Khoảng biểu đồ
                <select name="chart_range">
                    @foreach (['7d' => '7 ngày', '30d' => '30 ngày', 'this_month' => 'Tháng này', 'custom' => 'Tùy chỉnh'] as $value => $label)
                        <option value="{{ $value }}" @selected(($payload['filters']['chart_range'] ?? '30d') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-primary" type="submit">Áp dụng</button>
        </form>
    </div>

    <div class="card" style="padding: 16px 20px;">
        <div class="row" style="margin-top: 12px;">
            <div style="flex: 1 1 280px;">
                <div class="small muted">Khoảng thời gian</div>
                <strong>{{ $payload['filters']['date_range_label'] }}</strong>
            </div>
        </div>
    </div>

    <div class="grid cols-3">
        @foreach ($payload['metrics'] as $label => $value)
            <div class="metric">
                <div class="muted small">{{ $labels::metric($label) }}</div>
                <div class="value">{{ is_numeric($value) ? number_format((float) $value) : $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid cols-2" style="margin-top: 20px;">
        <div class="card" style="grid-column: 1 / -1;">
            <div class="row between" style="margin-bottom: 12px;">
                <div>
                    <h3 style="margin-bottom: 4px;">Bieu do doanh thu</h3>
                    <div class="small muted">{{ $payload['filters']['chart_period_label'] }}</div>
                </div>
                <span class="badge">{{ $payload['filters']['chart_range_label'] }}</span>
            </div>
            @if($hasRevenueChartData)
                <div style="height: 320px;">
                    <canvas id="dashboard-revenue-chart" data-dashboard-chart="revenue"></canvas>
                </div>
            @else
                <div class="empty">Chua co doanh thu trong khoang bieu do nay.</div>
            @endif
        </div>

        <div class="card">
            <h3>Trang thai don hang</h3>
            @if($hasOrderStatusChartData)
                <div style="height: 280px;">
                    <canvas id="dashboard-order-status-chart" data-dashboard-chart="order-status"></canvas>
                </div>
            @else
                <div class="empty">Chua co don hang de thong ke trang thai.</div>
            @endif
        </div>

        <div class="card">
            <h3>Top san pham ban chay</h3>
            @if($hasTopProductChartData)
                <div style="height: 280px;">
                    <canvas id="dashboard-top-products-chart" data-dashboard-chart="top-products"></canvas>
                </div>
            @else
                <div class="empty">Chua co san pham ban thanh cong.</div>
            @endif
        </div>
    </div>

    <div class="grid cols-2" style="margin-top: 20px;">
        <div class="card">
            <h3>Đơn hàng gần đây</h3>
            @if (empty($payload['recent_orders']))
                <div class="empty">Chưa có đơn hàng nào.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Đơn hàng</th>
                            <th>Khách hàng</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payload['recent_orders'] as $order)
                            <tr>
                                <td>{{ $order['order_no'] }}</td>
                                <td>{{ $order['customer']['full_name'] ?? 'Khách' }}</td>
                                <td>{{ $labels::orderStatus($order['status']) }}</td>
                                <td>{{ number_format((float) $order['total_amount']) }}</td>
                                 {{-- <td>{{date('d/m/Y H:i',strtotime($order['created_at']))}}</td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <h3>Hàng chờ xử lý</h3>
            <div class="stack">
                @foreach ($payload['work_queue'] as $item)
                    @if($canViewOrderDetails)
                        <a class="metric card-link" href="{{ route('admin-web.orders.index', ['queue' => $item['key']]) }}">
                            <div class="row between">
                                <strong>{{ $item['label'] }}</strong>
                                <span
                                    class="badge {{ $item['count'] > 0 ? 'warning' : 'success' }}">{{ $item['count'] }}</span>
                            </div>
                            <div class="small muted" style="margin-top: 8px;">
                                Đã nạp {{ count($item['orders']) }} đơn mẫu cho nhóm việc này.
                            </div>
                            <div class="small" style="margin-top: 10px; font-weight: 600;">
                                Bấm để xem chi tiết
                            </div>
                        </a>
                    @else
                        <div class="metric">
                            <div class="row between">
                                <strong>{{ $item['label'] }}</strong>
                                <span
                                    class="badge {{ $item['count'] > 0 ? 'warning' : 'success' }}">{{ $item['count'] }}</span>
                            </div>
                            <div class="small muted" style="margin-top: 8px;">
                                Đã nạp {{ count($item['orders']) }} đơn mẫu cho nhóm việc này.
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h3>Khách hàng nổi bật</h3>
            @if (empty($payload['top_customers']))
                <div class="empty">Chưa có khách hàng mua thành công.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số đơn</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payload['top_customers'] as $customer)
                            <tr>
                                <td>{{ $customer['full_name'] }}</td>
                                <td>{{ $customer['email'] }}</td>
                                <td>{{ $customer['successful_orders'] }}</td>
                                <td>{{ number_format((float) $customer['total_revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <h3>Sản phẩm sắp hết hàng</h3>
            @if (empty($payload['low_stock_products']))
                <div class="empty">Chưa có sản phẩm nào dưới ngưỡng tồn kho thấp đã cấu hình.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>SKU</th>
                            <th>Tồn kho</th>
                            <th>Giá bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payload['low_stock_products'] as $product)
                            <tr>
                                <td>{{ $product['name'] }}</td>
                                <td>{{ $product['sku'] }}</td>
                                <td>{{ $product['stock_quantity'] }}</td>
                                <td>{{ number_format((float) $product['sale_price']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const chartData = @json($dashboardChartData);
            const currencyFormatter = new Intl.NumberFormat("vi-VN", {
                style: "currency",
                currency: "VND",
                maximumFractionDigits: 0,
            });
            const numberFormatter = new Intl.NumberFormat("vi-VN");
            const colors = ["#14532d", "#2563eb", "#d97706", "#7c3aed", "#dc2626", "#0891b2", "#64748b"];

            Chart.defaults.font.family = '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif';
            Chart.defaults.color = "#5e6d81";

            const revenueCanvas = document.getElementById("dashboard-revenue-chart");
            if (revenueCanvas) {
                new Chart(revenueCanvas, {
                    data: {
                        labels: chartData.revenue.labels,
                        datasets: [
                            {
                                type: "bar",
                                label: "Doanh thu",
                                data: chartData.revenue.revenue,
                                backgroundColor: "rgba(20, 83, 45, 0.22)",
                                borderColor: "#14532d",
                                borderWidth: 1,
                                borderRadius: 6,
                                yAxisID: "y",
                            },
                            {
                                type: "line",
                                label: "Don thanh cong",
                                data: chartData.revenue.orders,
                                borderColor: "#2563eb",
                                backgroundColor: "#2563eb",
                                pointRadius: 3,
                                tension: 0.32,
                                yAxisID: "y1",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: "index", intersect: false },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: (value) => currencyFormatter.format(value) },
                            },
                            y1: {
                                beginAtZero: true,
                                position: "right",
                                grid: { drawOnChartArea: false },
                                ticks: { precision: 0 },
                            },
                        },
                        plugins: {
                            legend: { position: "bottom" },
                            tooltip: {
                                callbacks: {
                                    label: (context) => context.dataset.yAxisID === "y"
                                        ? `${context.dataset.label}: ${currencyFormatter.format(context.parsed.y)}`
                                        : `${context.dataset.label}: ${numberFormatter.format(context.parsed.y)}`,
                                },
                            },
                        },
                    },
                });
            }

            const statusCanvas = document.getElementById("dashboard-order-status-chart");
            if (statusCanvas) {
                new Chart(statusCanvas, {
                    type: "doughnut",
                    data: {
                        labels: chartData.orderStatus.labels,
                        datasets: [{
                            data: chartData.orderStatus.counts,
                            backgroundColor: colors,
                            borderColor: "#ffffff",
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: "62%",
                        plugins: {
                            legend: { position: "bottom" },
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label}: ${numberFormatter.format(context.parsed)}`,
                                },
                            },
                        },
                    },
                });
            }

            const productCanvas = document.getElementById("dashboard-top-products-chart");
            if (productCanvas) {
                new Chart(productCanvas, {
                    type: "bar",
                    data: {
                        labels: chartData.topProducts.labels,
                        datasets: [{
                            label: "So luong ban",
                            data: chartData.topProducts.sold,
                            backgroundColor: "rgba(37, 99, 235, 0.22)",
                            borderColor: "#2563eb",
                            borderWidth: 1,
                            borderRadius: 6,
                            revenue: chartData.topProducts.revenue,
                        }],
                    },
                    options: {
                        indexAxis: "y",
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 } },
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const sold = numberFormatter.format(context.parsed.x);
                                        const revenue = currencyFormatter.format(context.dataset.revenue[context.dataIndex] ?? 0);

                                        return `Da ban: ${sold} - Doanh thu: ${revenue}`;
                                    },
                                },
                            },
                        },
                    },
                });
            }
        })();
    </script>
@endpush
