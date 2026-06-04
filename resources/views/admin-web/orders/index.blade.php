@extends('admin-web.layouts.app')

@section('title', 'Điều phối đơn hàng')

@section('content')
    @php($labels = \App\Support\AdminWebLabel::class)

    <div class="toolbar">
        <div>
            <h2>Điều phối đơn hàng</h2>
            @if($activeQueueLabel)
                <p>Đang xem: {{ $activeQueueLabel }}</p>
            @endif
        </div>
    </div>

    <div class="card">
        <form class="filters" method="GET" action="{{ route('admin-web.orders.index') }}">
            <label>
                Từ khóa
                <input type="text" name="keyword" value="{{ request('keyword') }}">
            </label>
            <label>
                Trạng thái
                <select name="status">
                    <option value="">Tất cả</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $labels::orderStatus($status) }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Nhóm việc
                <select name="queue">
                    <option value="">Tất cả</option>
                    @foreach($queueOptions as $queueKey => $queueLabel)
                        <option value="{{ $queueKey }}" @selected(request('queue') === $queueKey)>{{ $queueLabel }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-secondary" type="submit">Lọc</button>
        </form>

        <form method="POST" action="{{ route('admin-web.orders.bulk-status') }}">
            @csrf
            <div class="row" style="margin-bottom: 14px;">
                <label>
                    Thao tác hàng loạt
                    <select name="action">
                        @foreach(['CONFIRM', 'SHIP', 'DELIVER', 'MARK_DELIVERY_FAILED', 'CANCEL', 'RESHIP'] as $action)
                            <option value="{{ $action }}">{{ $labels::bulkOrderAction($action) }}</option>
                        @endforeach
                    </select>
                </label>
                <label style="min-width: 260px;">
                    Ghi chú
                    <input type="text" name="note">
                </label>
                <button class="btn btn-primary" type="submit">Chạy thao tác</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Chọn</th>
                        <th>Đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th>Tổng tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><input type="checkbox" name="orderIds[]" value="{{ $order->id }}"></td>
                            <td>{{ $order->order_no }}</td>
                            <td>{{ $order->user?->full_name ?? $order->recipient_name }}</td>
                            <td>{{ $labels::orderStatus($order->status) }}</td>
                            <td>{{ $labels::paymentStatus($order->payment?->payment_status) }}</td>
                            <td>{{ number_format((float) $order->total_amount) }}</td>
                            <td><a class="btn btn-secondary" href="{{ route('admin-web.orders.show', $order->id) }}">Chi tiết</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
        @include('admin-web.partials.pagination', ['paginator' => $orders])
    </div>
@endsection
