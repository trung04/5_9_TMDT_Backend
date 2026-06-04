@extends('admin-web.layouts.app')

@section('title', 'Sửa khách hàng')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Sửa khách hàng</h2>
            <p>{{ $user->full_name }}</p>
        </div>
        <div class="toolbar-actions">
            <a class="btn btn-secondary" href="{{ route('admin-web.users.orders.index', $user->id) }}">Xem đơn hàng</a>
            @if($adminUser->hasAdminPermission('admin.users.delete'))
                <form method="POST" action="{{ route('admin-web.users.destroy', $user->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Ngừng kích hoạt</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        @include('admin-web.users._form', [
            'user' => $user,
            'formAction' => route('admin-web.users.update', $user->id),
            'formMethod' => 'PUT',
            'submitLabel' => 'Lưu thay đổi',
            'showPasswordField' => false,
        ])
    </div>
@endsection
