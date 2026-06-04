@extends('admin-web.layouts.app')

@section('title', 'Bảng điều khiển')

@section('content')
    @php($labels = \App\Support\AdminWebLabel::class)
    @php($canViewOrderDetails = $adminUser->hasAdminPermission('admin.orders.view'))

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
