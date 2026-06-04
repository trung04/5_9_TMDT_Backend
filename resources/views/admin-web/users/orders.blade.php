@extends('admin-web.layouts.app')

@section('title', 'Đơn hàng khách hàng')

@section('content')
    @php($labels = \App\Support\AdminWebLabel::class)

    <div class="toolbar">
        <div>
            <h2>Đơn hàng của {{ $customer->full_name }}</h2>
            <p>Lịch sử đơn hàng của khách hàng được hiển thị phía máy chủ bằng dịch vụ đơn hàng dùng chung.</p>
        </div>
        <a class="btn btn-secondary" href="{{ route('admin-web.users.index') }}">Quay lại khách hàng</a>
    </div>

    <div class="card">
        <form class="filters" method="GET" action="{{ route('admin-web.users.orders.index', $customer->id) }}">
            <label>
                Từ khóa
                <input type="text" name="keyword" value="{{ request('keyword') }}">
            </label>
            <label>
                Trạng thái
                <select name="status">
                    <option value="">Tất cả</option>
                    @foreach(\App\Models\Order::allowedStatuses() as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $labels::orderStatus($status) }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-secondary" type="submit">Lọc</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Đơn hàng</th>
                    <th>Trạng thái</th>
                    <th>Thanh toán</th>
                    <th>Tổng tiền</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_no }}</td>
                        <td>{{ $labels::orderStatus($order->status) }}</td>
                        <td>{{ $labels::paymentStatus($order->payment?->payment_status) }}</td>
                        <td>{{ number_format((float) $order->total_amount) }}</td>
                        <td><a class="btn btn-secondary" href="{{ route('admin-web.orders.show', $order->id) }}">Mở đơn hàng</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('admin-web.partials.pagination', ['paginator' => $orders])
    </div>
@endsection
