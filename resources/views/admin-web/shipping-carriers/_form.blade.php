@php
    $labels = \App\Support\AdminWebLabel::class;
    $providers = \App\Models\ShippingCarrier::allowedProviders();
    $provinces = $provinces ?? [];
    $selectedPickupProvinceId = (int) old('pickup_province_id', $carrier->pickup_province_id ?? 0);
@endphp

<form method="POST" action="{{ $formAction }}" class="form-grid">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <label>
        Ma
        <input type="text" name="code" value="{{ old('code', $carrier->code ?? '') }}" required>
    </label>
    <label>
        Ten
        <input type="text" name="name" value="{{ old('name', $carrier->name ?? '') }}" required>
    </label>
    <label>
        Nha cung cap
        <select name="provider">
            @foreach($providers as $provider)
                <option value="{{ $provider }}" @selected(old('provider', $carrier->provider ?? \App\Models\ShippingCarrier::PROVIDER_MANUAL) === $provider)>{{ $labels::shippingProvider($provider) }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Mau URL theo doi
        <input type="text" name="tracking_url_template" value="{{ old('tracking_url_template', $carrier->tracking_url_template ?? '') }}">
    </label>

    <label>
        Can nang mac dinh (gram)
        <input type="number" min="1" name="default_weight" value="{{ old('default_weight', $carrier->default_weight ?? 1000) }}">
    </label>
    <label>
        Dai mac dinh (cm)
        <input type="number" min="1" name="default_length" value="{{ old('default_length', $carrier->default_length ?? 20) }}">
    </label>
    <label>
        Rong mac dinh (cm)
        <input type="number" min="1" name="default_width" value="{{ old('default_width', $carrier->default_width ?? 20) }}">
    </label>
    <label>
        Cao mac dinh (cm)
        <input type="number" min="1" name="default_height" value="{{ old('default_height', $carrier->default_height ?? 10) }}">
    </label>
    <label>
        Loai dich vu mac dinh
        <select name="default_service_type_id">
            <option value="2" @selected((int) old('default_service_type_id', $carrier->default_service_type_id ?? 2) === 2)>2 - Thuong mai dien tu</option>
            <option value="5" @selected((int) old('default_service_type_id', $carrier->default_service_type_id ?? 2) === 5)>5 - Hang nhe</option>
            <option value="1" @selected((int) old('default_service_type_id', $carrier->default_service_type_id ?? 2) === 1)>1 - Dich vu nhanh</option>
        </select>
    </label>
    <label>
        Ben tra phi mac dinh
        <select name="default_payment_type_id">
            <option value="1" @selected((int) old('default_payment_type_id', $carrier->default_payment_type_id ?? 1) === 1)>Shop tra phi</option>
            <option value="2" @selected((int) old('default_payment_type_id', $carrier->default_payment_type_id ?? 1) === 2)>Khach tra phi</option>
        </select>
    </label>
    <label class="full">
        Ghi chu bat buoc mac dinh
        <select name="default_required_note">
            @foreach(['KHONGCHOXEMHANG' => 'Khong cho xem hang', 'CHOXEMHANGKHONGTHU' => 'Cho xem hang khong thu', 'CHOTHUHANG' => 'Cho thu hang'] as $value => $label)
                <option value="{{ $value }}" @selected(old('default_required_note', $carrier->default_required_note ?? 'KHONGCHOXEMHANG') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label>
        Ten diem lay hang
        <input type="text" name="pickup_name" value="{{ old('pickup_name', $carrier->pickup_name ?? '') }}">
    </label>
    <label>
        So dien thoai lay hang
        <input type="text" name="pickup_phone" value="{{ old('pickup_phone', $carrier->pickup_phone ?? '') }}">
    </label>
    <label class="full">
        Dia chi lay hang
        <input type="text" name="pickup_address" value="{{ old('pickup_address', $carrier->pickup_address ?? '') }}">
    </label>
    <label>
        Tinh/thanh lay hang
        <input type="hidden" name="pickup_province_name" value="{{ old('pickup_province_name', $carrier->pickup_province_name ?? '') }}">
        <select name="pickup_province_id" data-location-level="province" data-location-next="[name='pickup_district_id']" data-location-name-target="pickup_province_name">
            <option value="">Chon</option>
            @foreach($provinces as $province)
                <option value="{{ $province['ProvinceID'] }}" @selected($selectedPickupProvinceId === $province['ProvinceID'])>{{ $province['ProvinceName'] }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Quan/huyen lay hang
        <input type="hidden" name="pickup_district_name" value="{{ old('pickup_district_name', $carrier->pickup_district_name ?? '') }}">
        <select name="pickup_district_id" data-location-level="district" data-location-next="[name='pickup_ward_code']" data-location-name-target="pickup_district_name">
            <option value="{{ old('pickup_district_id', $carrier->pickup_district_id ?? '') }}">{{ old('pickup_district_name', $carrier->pickup_district_name ?? 'Chon') }}</option>
        </select>
    </label>
    <label>
        Phuong/xa lay hang
        <input type="hidden" name="pickup_ward_name" value="{{ old('pickup_ward_name', $carrier->pickup_ward_name ?? '') }}">
        <select name="pickup_ward_code" data-location-name-target="pickup_ward_name">
            <option value="{{ old('pickup_ward_code', $carrier->pickup_ward_code ?? '') }}">{{ old('pickup_ward_name', $carrier->pickup_ward_name ?? 'Chon') }}</option>
        </select>
    </label>
    <label>
        Dang hoat dong
        <select name="is_active">
            <option value="1" @selected((bool) old('is_active', $carrier->is_active ?? true))>{{ $labels::active(true) }}</option>
            <option value="0" @selected(! (bool) old('is_active', $carrier->is_active ?? true))>{{ $labels::active(false) }}</option>
        </select>
    </label>
    <label>
        Da xoa
        <select name="is_deleted">
            <option value="0" @selected(! (bool) old('is_deleted', $carrier->is_deleted ?? false))>{{ $labels::yesNo(false) }}</option>
            <option value="1" @selected((bool) old('is_deleted', $carrier->is_deleted ?? false))>{{ $labels::yesNo(true) }}</option>
        </select>
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        @if($adminUser->hasAdminPermission('admin.shipping_carriers.view'))
            <a class="btn btn-secondary" href="{{ route('admin-web.shipping-carriers.index') }}">Quay lai don vi van chuyen</a>
        @endif
    </div>
</form>
