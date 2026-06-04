@extends('admin-web.layouts.app')

@section('title', 'Tạo tài khoản admin')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Tạo tài khoản admin</h2>
            <p>Tạo mới tài khoản quản trị trên một trang riêng.</p>
        </div>
    </div>

    <div class="card">
        @include('admin-web.admins._form', [
            'admin' => $admin,
            'formAction' => route('admin-web.admins.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Tạo tài khoản admin',
            'showPasswordField' => true,
        ])
    </div>
@endsection
