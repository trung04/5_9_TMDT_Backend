<form method="POST" action="{{ route('admin-web.community.invitations.store') }}" class="form-grid">
    @csrf
    <label>
        Tên nhà cung cấp
        <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" required>
    </label>
    <label>
        Người liên hệ
        <input type="text" name="contact_name" value="{{ old('contact_name') }}" required>
    </label>
    <label>
        Email
        <input type="email" name="email" value="{{ old('email') }}" required>
    </label>
    <label class="full">
        Danh mục
        <input type="text" name="categories[]" value="{{ old('categories.0') }}" placeholder="Phiên bản SSR đầu tiên chỉ gửi 1 danh mục mỗi lần">
    </label>
    <label class="full">
        Ghi chú
        <textarea name="note">{{ old('note') }}</textarea>
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">Tạo lời mời</button>
        @if($adminUser->hasAdminPermission('admin.community.view'))
            <a class="btn btn-secondary" href="{{ route('admin-web.community.index') }}">Quay lại cộng đồng</a>
        @endif
    </div>
</form>
