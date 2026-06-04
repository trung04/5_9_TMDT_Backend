@extends('admin-web.layouts.app')

@section('title', 'Cài đặt')

@section('content')
    @php($labels = \App\Support\AdminWebLabel::class)

    <div class="toolbar">
        <div>
            <h2>Cài đặt</h2>
            <p>Cấu hình riêng cho quản trị được cấp bởi dịch vụ cài đặt hiện có.</p>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin-web.settings.update') }}" class="form-grid">
            @csrf
            @method('PUT')
            <label>
                Tên cửa hàng
                <input type="text" name="store_name" value="{{ old('store_name', $settings->store_name) }}" required>
            </label>
            <label>
                Email hỗ trợ
                <input type="email" name="support_email" value="{{ old('support_email', $settings->support_email) }}">
            </label>
            <label>
                Số điện thoại hỗ trợ
                <input type="text" name="support_phone" value="{{ old('support_phone', $settings->support_phone) }}">
            </label>
            <label>
                Ngưỡng tồn kho thấp
                <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}" min="1" required>
            </label>
            <label>
                Số giây làm mới bảng điều khiển
                <input type="number" name="dashboard_refresh_seconds" value="{{ old('dashboard_refresh_seconds', $settings->dashboard_refresh_seconds) }}" min="15" required>
            </label>
            <label class="full">
                Ghi chú
                <textarea name="notes">{{ old('notes', $settings->notes) }}</textarea>
            </label>
            <label>
                <span>Tự động xác nhận đơn</span>
                <select name="order_auto_confirm">
                    <option value="0" @selected(! old('order_auto_confirm', $settings->order_auto_confirm))>{{ $labels::enabled(false) }}</option>
                    <option value="1" @selected((bool) old('order_auto_confirm', $settings->order_auto_confirm))>{{ $labels::enabled(true) }}</option>
                </select>
            </label>
            <label>
                <span>Gửi tổng hợp hằng ngày</span>
                <select name="send_daily_summary">
                    <option value="0" @selected(! old('send_daily_summary', $settings->send_daily_summary))>{{ $labels::enabled(false) }}</option>
                    <option value="1" @selected((bool) old('send_daily_summary', $settings->send_daily_summary))>{{ $labels::enabled(true) }}</option>
                </select>
            </label>
            <label>
                <span>Chế độ bảo trì</span>
                <select name="maintenance_mode">
                    <option value="0" @selected(! old('maintenance_mode', $settings->maintenance_mode))>{{ $labels::enabled(false) }}</option>
                    <option value="1" @selected((bool) old('maintenance_mode', $settings->maintenance_mode))>{{ $labels::enabled(true) }}</option>
                </select>
            </label>
            <div class="full">
                <button class="btn btn-primary" type="submit">Lưu cài đặt</button>
            </div>
        </form>
    </div>
@endsection
