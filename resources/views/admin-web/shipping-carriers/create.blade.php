@extends('admin-web.layouts.app')

@section('title', 'Tạo đơn vị vận chuyển')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Tạo đơn vị vận chuyển</h2>
            <p>Dùng trang riêng để đăng ký đơn vị vận chuyển mới.</p>
        </div>
    </div>

    <div class="card">
        @include('admin-web.shipping-carriers._form', [
            'carrier' => $carrier,
            'formAction' => route('admin-web.shipping-carriers.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Tạo đơn vị vận chuyển',
        ])
    </div>
@endsection
