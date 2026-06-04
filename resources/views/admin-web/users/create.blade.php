@extends('admin-web.layouts.app')

@section('title', 'Tạo khách hàng')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Tạo khách hàng</h2>
            <p>Mở trang riêng để tạo tài khoản khách hàng mới.</p>
        </div>
    </div>

    <div class="card">
        @include('admin-web.users._form', [
            'user' => $user,
            'formAction' => route('admin-web.users.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Tạo khách hàng',
            'showPasswordField' => true,
        ])
    </div>
@endsection
