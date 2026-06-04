@php($labels = \App\Support\AdminWebLabel::class)

<form method="POST" action="{{ $formAction }}" class="form-grid">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif
    <label>
        Họ tên
        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name ?? '') }}" required>
    </label>
    <label>
        Email
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
    </label>
    <label>
        Số điện thoại
        <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" required>
    </label>
    @if($showPasswordField)
        <label>
            Mật khẩu
            <input type="password" name="password" required>
        </label>
    @endif
    <label class="full">
        Địa chỉ
        <input type="text" name="address" value="{{ old('address', $user->address ?? '') }}">
    </label>
    <label>
        Thành phố
        <input type="text" name="city" value="{{ old('city', $user->city ?? '') }}">
    </label>
    <label>
        Khu vực yêu thích
        <input type="text" name="favorite_region" value="{{ old('favorite_region', $user->favorite_region ?? '') }}">
    </label>
    <label>
        Đang hoạt động
        <select name="is_active">
            <option value="1" @selected((bool) old('is_active', $user->is_active ?? true))>{{ $labels::active(true) }}</option>
            <option value="0" @selected(! (bool) old('is_active', $user->is_active ?? true))>{{ $labels::active(false) }}</option>
        </select>
    </label>
    <label>
        Đã xóa
        <select name="is_deleted">
            <option value="0" @selected(! (bool) old('is_deleted', $user->is_deleted ?? false))>{{ $labels::yesNo(false) }}</option>
            <option value="1" @selected((bool) old('is_deleted', $user->is_deleted ?? false))>{{ $labels::yesNo(true) }}</option>
        </select>
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        <a class="btn btn-secondary" href="{{ route('admin-web.users.index') }}">Quay lại khách hàng</a>
    </div>
</form>
