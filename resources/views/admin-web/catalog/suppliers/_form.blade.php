@php($labels = \App\Support\AdminWebLabel::class)

<form method="POST" action="{{ $formAction }}" class="form-grid">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif
    <label>
        Mã nhà cung cấp
        <input type="text" name="supplier_code" value="{{ old('supplier_code', $supplier->supplier_code ?? '') }}" required>
    </label>
    <label>
        Tên
        <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required>
    </label>
    <label>
        Người liên hệ
        <input type="text" name="contact_name" value="{{ old('contact_name', $supplier->contact_name ?? '') }}">
    </label>
    <label>
        Số điện thoại
        <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}" required>
    </label>
    <label>
        Email
        <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}">
    </label>
    <label class="full">
        Địa chỉ
        <input type="text" name="address" value="{{ old('address', $supplier->address ?? '') }}">
    </label>
    <label>
        Đang hoạt động
        <select name="is_active">
            <option value="1" @selected((bool) old('is_active', $supplier->is_active ?? true))>{{ $labels::active(true) }}</option>
            <option value="0" @selected(! (bool) old('is_active', $supplier->is_active ?? true))>{{ $labels::active(false) }}</option>
        </select>
    </label>
    <label>
        Đã xóa
        <select name="is_deleted">
            <option value="0" @selected(! (bool) old('is_deleted', $supplier->is_deleted ?? false))>{{ $labels::yesNo(false) }}</option>
            <option value="1" @selected((bool) old('is_deleted', $supplier->is_deleted ?? false))>{{ $labels::yesNo(true) }}</option>
        </select>
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        <a class="btn btn-secondary" href="{{ route('admin-web.suppliers.index') }}">Quay lại nhà cung cấp</a>
    </div>
</form>
