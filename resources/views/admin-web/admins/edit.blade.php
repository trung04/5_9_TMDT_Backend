@extends('admin-web.layouts.app')

@section('title', 'Sửa tài khoản admin')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Sửa tài khoản admin</h2>
            <p>{{ $admin->full_name }}</p>
        </div>
        <div class="toolbar-actions">
            <form method="POST" action="{{ route('admin-web.admins.status', $admin->id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="is_active" value="{{ $admin->is_active ? 0 : 1 }}">
                <input type="hidden" name="is_deleted" value="{{ $admin->is_active ? 1 : 0 }}">
                <button class="btn btn-warning" type="submit">{{ $admin->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}</button>
            </form>
        </div>
    </div>

    <div class="grid cols-2">
        <div class="card">
            @include('admin-web.admins._form', [
                'admin' => $admin,
                'formAction' => route('admin-web.admins.update', $admin->id),
                'formMethod' => 'PUT',
                'submitLabel' => 'Lưu tài khoản admin',
                'showPasswordField' => false,
            ])
        </div>

        <div class="card">
            <h3>Đặt lại mật khẩu</h3>
            <form method="POST" action="{{ route('admin-web.admins.password', $admin->id) }}" class="form-grid">
                @csrf
                @method('PATCH')
                <label class="full">
                    Mật khẩu mới
                    <input type="password" name="password" required>
                </label>
                <div class="full row">
                    <button class="btn btn-warning" type="submit">Cập nhật mật khẩu</button>
                </div>
            </form>

            <div class="metric" style="margin-top: 20px;">
                <strong>Thông tin tài khoản</strong>
                <div class="small muted" style="margin-top: 10px;">Email: {{ $admin->email }}</div>
                <div class="small muted">Số điện thoại: {{ $admin->phone }}</div>
                <div class="small muted">Trạng thái: {{ $admin->is_active && ! $admin->is_deleted ? 'Đang hoạt động' : 'Đã khóa' }}</div>
                <div class="small muted">Được tạo bởi: {{ $admin->createdByAdmin?->full_name ?? 'Hệ thống' }}</div>
            </div>
        </div>
    </div>
@endsection
