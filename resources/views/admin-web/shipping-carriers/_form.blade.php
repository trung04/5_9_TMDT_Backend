@php($labels = \App\Support\AdminWebLabel::class)

<form method="POST" action="{{ $formAction }}" class="form-grid">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif
    <label>
        Mã
        <input type="text" name="code" value="{{ old('code', $carrier->code ?? '') }}" required>
    </label>
    <label>
        Tên
        <input type="text" name="name" value="{{ old('name', $carrier->name ?? '') }}" required>
    </label>
    <label>
        Nhà cung cấp
        <select name="provider">
            @foreach(\App\Models\ShippingCarrier::allowedProviders() as $provider)
                <option value="{{ $provider }}" @selected(old('provider', $carrier->provider ?? \App\Models\ShippingCarrier::PROVIDER_MANUAL) === $provider)>{{ $labels::shippingProvider($provider) }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Mẫu URL theo dõi
        <input type="text" name="tracking_url_template" value="{{ old('tracking_url_template', $carrier->tracking_url_template ?? '') }}">
    </label>
    <label>
        Khối lượng mặc định
        <input type="number" name="default_weight" value="{{ old('default_weight', $carrier->default_weight ?? 1000) }}">
    </label>
    <label>
        Loại dịch vụ mặc định
        <input type="number" name="default_service_type_id" value="{{ old('default_service_type_id', $carrier->default_service_type_id ?? 2) }}">
    </label>
    <label>
        Tên điểm lấy hàng
        <input type="text" name="pickup_name" value="{{ old('pickup_name', $carrier->pickup_name ?? '') }}">
    </label>
    <label>
        Số điện thoại lấy hàng
        <input type="text" name="pickup_phone" value="{{ old('pickup_phone', $carrier->pickup_phone ?? '') }}">
    </label>
    <label class="full">
        Địa chỉ lấy hàng
        <input type="text" name="pickup_address" value="{{ old('pickup_address', $carrier->pickup_address ?? '') }}">
    </label>
    <label>
        Đang hoạt động
        <select name="is_active">
            <option value="1" @selected((bool) old('is_active', $carrier->is_active ?? true))>{{ $labels::active(true) }}</option>
            <option value="0" @selected(! (bool) old('is_active', $carrier->is_active ?? true))>{{ $labels::active(false) }}</option>
        </select>
    </label>
    <label>
        Đã xóa
        <select name="is_deleted">
            <option value="0" @selected(! (bool) old('is_deleted', $carrier->is_deleted ?? false))>{{ $labels::yesNo(false) }}</option>
            <option value="1" @selected((bool) old('is_deleted', $carrier->is_deleted ?? false))>{{ $labels::yesNo(true) }}</option>
        </select>
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        @if($adminUser->hasAdminPermission('admin.shipping_carriers.view'))
            <a class="btn btn-secondary" href="{{ route('admin-web.shipping-carriers.index') }}">Quay lại đơn vị vận chuyển</a>
        @endif
    </div>
</form>
