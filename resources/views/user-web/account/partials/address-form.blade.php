@php
    $isEdit = (bool) $address;
@endphp
<form class="mt-5 grid gap-4 sm:grid-cols-2" action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif
    <label class="space-y-2">
        <span class="text-sm font-semibold text-on-surface-variant">Nhãn</span>
        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="label" value="{{ old('label', $address['label'] ?? '') }}">
    </label>
    <label class="space-y-2">
        <span class="text-sm font-semibold text-on-surface-variant">Người nhận</span>
        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="recipient" value="{{ old('recipient', $address['recipient'] ?? '') }}">
    </label>
    <label class="space-y-2">
        <span class="text-sm font-semibold text-on-surface-variant">Điện thoại</span>
        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="phone" value="{{ old('phone', $address['phone'] ?? '') }}">
    </label>
    <label class="space-y-2">
        <span class="text-sm font-semibold text-on-surface-variant">Thành phố</span>
        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="city" value="{{ old('city', $address['city'] ?? ($address['ghn_province_name'] ?? '')) }}">
    </label>
    <label class="space-y-2 sm:col-span-2">
        <span class="text-sm font-semibold text-on-surface-variant">Địa chỉ cụ thể</span>
        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="line1" value="{{ old('line1', $address['line1'] ?? '') }}">
    </label>
    <label class="space-y-2">
        <span class="text-sm font-semibold text-on-surface-variant">Tỉnh/Thành</span>
        <input type="hidden" name="ghn_province_name" value="{{ old('ghn_province_name', $address['ghn_province_name'] ?? '') }}">
        <select class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="ghn_province_id" data-location-level="province" data-location-next="[name='ghn_district_id']" data-location-name-target="ghn_province_name">
            <option value="">Chọn</option>
            @foreach($provinces as $province)
                <option value="{{ $province['ProvinceID'] }}" @selected((int) old('ghn_province_id', $address['ghn_province_id'] ?? 0) === $province['ProvinceID'])>{{ $province['ProvinceName'] }}</option>
            @endforeach
        </select>
    </label>
    <label class="space-y-2">
        <span class="text-sm font-semibold text-on-surface-variant">Quận/Huyện</span>
        <input type="hidden" name="ghn_district_name" value="{{ old('ghn_district_name', $address['ghn_district_name'] ?? '') }}">
        <select class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="ghn_district_id" data-location-level="district" data-location-next="[name='ghn_ward_code']" data-location-name-target="ghn_district_name">
            <option value="{{ old('ghn_district_id', $address['ghn_district_id'] ?? '') }}">{{ old('ghn_district_name', $address['ghn_district_name'] ?? 'Chọn') }}</option>
        </select>
    </label>
    <label class="space-y-2">
        <span class="text-sm font-semibold text-on-surface-variant">Phường/Xã</span>
        <input type="hidden" name="ghn_ward_name" value="{{ old('ghn_ward_name', $address['ghn_ward_name'] ?? '') }}">
        <select class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="ghn_ward_code" data-location-name-target="ghn_ward_name">
            <option value="{{ old('ghn_ward_code', $address['ghn_ward_code'] ?? '') }}">{{ old('ghn_ward_name', $address['ghn_ward_name'] ?? 'Chọn') }}</option>
        </select>
    </label>
    <label class="space-y-2">
        <span class="text-sm font-semibold text-on-surface-variant">Ghi chú</span>
        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="note" value="{{ old('note', $address['note'] ?? '') }}">
    </label>
    <label class="rounded-2xl bg-surface-container-low p-4 text-sm font-semibold">
        <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address['is_default'] ?? false))>
        <span class="ml-2">Đặt làm mặc định</span>
    </label>
    <div class="sm:col-span-2">
        <button class="rounded-full bg-primary px-6 py-3 text-sm font-semibold text-on-primary" type="submit">{{ $isEdit ? 'Cập nhật địa chỉ' : 'Thêm địa chỉ' }}</button>
    </div>
</form>
