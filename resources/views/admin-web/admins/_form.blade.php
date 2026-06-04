<form method="POST" action="{{ $formAction }}" class="form-grid">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif
    <label>
        Họ tên
        <input type="text" name="full_name" value="{{ old('full_name', $admin->full_name ?? '') }}" required>
    </label>
    <label>
        Email
        <input type="email" name="email" value="{{ old('email', $admin->email ?? '') }}" required>
    </label>
    <label>
        Số điện thoại
        <input type="text" name="phone" value="{{ old('phone', $admin->phone ?? '') }}" required>
    </label>
    @if($showPasswordField)
        <label>
            Mật khẩu
            <input type="password" name="password" required>
        </label>
    @endif
    <label>
        Đang hoạt động
        <select name="is_active">
            <option value="1" @selected((bool) old('is_active', $admin->is_active ?? true))>Đang hoạt động</option>
            <option value="0" @selected(! (bool) old('is_active', $admin->is_active ?? true))>Ngừng hoạt động</option>
        </select>
    </label>
    <label>
        Đã xóa
        <select name="is_deleted">
            <option value="0" @selected(! (bool) old('is_deleted', $admin->is_deleted ?? false))>Không</option>
            <option value="1" @selected((bool) old('is_deleted', $admin->is_deleted ?? false))>Có</option>
        </select>
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        <a class="btn btn-secondary" href="{{ route('admin-web.admins.index') }}">Quay lại danh sách admin</a>
    </div>
</form>
