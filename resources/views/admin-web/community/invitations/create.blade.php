@extends('admin-web.layouts.app')

@section('title', 'Tạo lời mời nhà cung cấp')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Tạo lời mời nhà cung cấp</h2>
            <p>Mở trang riêng để tạo lời mời nhà cung cấp mà không trộn với bảng điều khiển cộng đồng.</p>
        </div>
    </div>

    <div class="card">
        @include('admin-web.community.invitations._form')
    </div>
@endsection
