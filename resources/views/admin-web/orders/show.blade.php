@extends('admin-web.layouts.app')

@section('title', 'Chi tiết đơn hàng')

@section('content')
    @php($labels = \App\Support\AdminWebLabel::class)

    <div class="toolbar">
        <div>
            <h2>{{ $order->order_no }}</h2>
            <p>Trang chi tiết cho các thao tác xử lý, thanh toán và vận đơn.</p>
        </div>
        <a class="btn btn-secondary" href="{{ route('admin-web.orders.index') }}">Quay lại điều phối</a>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h3>Tóm tắt đơn hàng</h3>
            <table>
                <tbody>
                    <tr><th>Khách hàng</th><td>{{ $orderPayload['customer']['full_name'] ?? $order->recipient_name }}</td></tr>
                    <tr><th>Trạng thái</th><td>{{ $labels::orderStatus($order->status) }}</td></tr>
                    <tr><th>Trạng thái thanh toán</th><td>{{ $labels::paymentStatus($order->payment?->payment_status) }}</td></tr>
                    <tr><th>Phương thức thanh toán</th><td>{{ $labels::paymentMethod($order->payment_method) }}</td></tr>
                    <tr><th>Tổng tiền</th><td>{{ number_format((float) $order->total_amount) }}</td></tr>
                    <tr><th>Địa chỉ giao hàng</th><td>{{ $order->shipping_address }}</td></tr>
                    <tr><th>Ghi chú</th><td>{{ $order->note ?: 'Không có' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Thao tác trạng thái</h3>
            <form method="POST" action="{{ route('admin-web.orders.status.update', $order->id) }}" class="form-grid">
                @csrf
                @method('PATCH')
                <label>
                    Trạng thái tiếp theo
                    <select name="status">
                        @foreach($allStatuses as $status)
                            <option value="{{ $status }}" @selected(old('status') === $status)>{{ $labels::orderStatus($status) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Hoàn kho
                    <select name="restock_inventory">
                        <option value="">Không thay đổi</option>
                        <option value="1">Có</option>
                        <option value="0">Không</option>
                    </select>
                </label>
                <label class="full">
                    Ghi chú
                    <textarea name="note">{{ old('note') }}</textarea>
                </label>
                <div class="full">
                    <button class="btn btn-primary" type="submit">Cập nhật trạng thái</button>
                </div>
            </form>

            <h3 style="margin-top: 22px;">Thao tác thanh toán</h3>
            <form method="POST" action="{{ route('admin-web.orders.payment.update', $order->id) }}" class="form-grid">
                @csrf
                @method('PATCH')
                <label>
                    Trạng thái thanh toán tiếp theo
                    <select name="payment_status">
                        @foreach($allPaymentStatuses as $status)
                            <option value="{{ $status }}" @selected(old('payment_status') === $status)>{{ $labels::paymentStatus($status) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="full">
                    Ghi chú
                    <textarea name="note">{{ old('payment_note') }}</textarea>
                </label>
                <div class="full">
                    <button class="btn btn-primary" type="submit">Cập nhật thanh toán</button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h3>Vận đơn</h3>
            <form method="POST" action="{{ route('admin-web.orders.shipment.store', $order->id) }}" class="form-grid">
                @csrf
                <label>
                    Đơn vị vận chuyển
                    <select name="shipping_carrier_id">
                        @foreach($shippingCarriers as $carrier)
                            <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Mã vận đơn
                    <input type="text" name="tracking_code" value="{{ old('tracking_code', $order->shipment?->tracking_code ?? '') }}">
                </label>
                <label class="full">
                    URL theo dõi
                    <input type="text" name="tracking_url" value="{{ old('tracking_url', $order->shipment?->tracking_url ?? '') }}">
                </label>
                <label class="full">
                    Ghi chú
                    <textarea name="note">{{ old('shipment_note') }}</textarea>
                </label>
                <div class="full">
                    <button class="btn btn-primary" type="submit">Tạo hoặc thay thế vận đơn</button>
                </div>
            </form>

            @if($order->shipment)
                <div class="row" style="margin-top: 14px;">
                    <form method="POST" action="{{ route('admin-web.orders.shipment.sync', $order->id) }}">
                        @csrf
                        <button class="btn btn-secondary" type="submit">Đồng bộ vận đơn</button>
                    </form>
                    <form method="POST" action="{{ route('admin-web.orders.shipment.destroy', $order->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Hủy vận đơn</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="card">
            <h3>Sản phẩm</h3>
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderPayload['items'] as $item)
                        <tr>
                            <td>{{ $item['product_name_snapshot'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>{{ number_format((float) $item['unit_price']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
