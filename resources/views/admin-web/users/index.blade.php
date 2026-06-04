@extends('admin-web.layouts.app')

@section('title', 'Khách hàng')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Khách hàng</h2>
            <p>Quản lý khách hàng với các trang SSR hỗ trợ mở trực tiếp và tải lại an toàn.</p>
        </div>
        @if($adminUser->hasAdminPermission('admin.users.create'))
            <a class="btn btn-primary" href="{{ route('admin-web.users.create') }}">Tạo khách hàng</a>
        @endif
    </div>

    <div class="card">
        <form class="filters" method="GET" action="{{ route('admin-web.users.index') }}">
            <label>
                Từ khóa
                <input type="text" name="keyword" value="{{ request('keyword') }}">
            </label>
            <button class="btn btn-secondary" type="submit">Lọc</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Đơn hàng</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->full_name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->orders_count }}</td>
                        <td>
                            <div class="row">
                                @if($adminUser->hasAdminPermission('admin.users.update'))
                                    <a class="btn btn-secondary" href="{{ route('admin-web.users.edit', $user->id) }}">Sửa</a>
                                @endif
                                @if($adminUser->hasAdminPermission('admin.orders.view'))
                                    <a class="btn btn-secondary" href="{{ route('admin-web.users.orders.index', $user->id) }}">Đơn hàng</a>
                                @endif
                                @if($adminUser->hasAdminPermission('admin.users.delete'))
                                    <form method="POST" action="{{ route('admin-web.users.destroy', $user->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">Ngừng kích hoạt</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('admin-web.partials.pagination', ['paginator' => $users])
    </div>
@endsection
