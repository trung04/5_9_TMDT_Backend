@extends('admin-web.layouts.app')

@section('title', 'Tài khoản admin')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Tài khoản admin</h2>
            <p>Quản lý danh sách tài khoản quản trị không còn phụ thuộc role hay permission.</p>
        </div>
        <div class="toolbar-actions">
            <form class="inline-form" method="GET" action="{{ route('admin-web.admins.index') }}">
                <label>
                    Tìm kiếm
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tên, email, số điện thoại">
                </label>
                <button class="btn btn-secondary" type="submit">Lọc</button>
            </form>
            <a class="btn btn-primary" href="{{ route('admin-web.admins.create') }}">Tạo tài khoản admin</a>
        </div>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Họ tên</th>
                    <th>Liên hệ</th>
                    <th>Trạng thái</th>
                    <th>Người tạo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                    <tr>
                        <td>{{ $admin->full_name }}</td>
                        <td>
                            {{ $admin->email }}<br>
                            <span class="muted small">{{ $admin->phone }}</span>
                        </td>
                        <td>
                            @if($admin->is_active && ! $admin->is_deleted)
                                <span class="badge success">Đang hoạt động</span>
                            @else
                                <span class="badge warning">Đã khóa</span>
                            @endif
                        </td>
                        <td>{{ $admin->createdByAdmin?->full_name ?? 'Hệ thống' }}</td>
                        <td>
                            <div class="row">
                                <a class="btn btn-secondary" href="{{ route('admin-web.admins.edit', $admin->id) }}">Sửa</a>
                                <form method="POST" action="{{ route('admin-web.admins.status', $admin->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $admin->is_active ? 0 : 1 }}">
                                    <input type="hidden" name="is_deleted" value="{{ $admin->is_active ? 1 : 0 }}">
                                    <button class="btn btn-warning" type="submit">{{ $admin->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty">Chưa có tài khoản admin nào.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @include('admin-web.partials.pagination', ['paginator' => $admins])
    </div>
@endsection
