@extends('admin-web.layouts.app')

@section('title', 'Tạo sản phẩm')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Tạo sản phẩm</h2>
            <p>Dùng trang biểu mẫu riêng để thêm sản phẩm mới.</p>
        </div>
    </div>

    <div class="card">
        @include('admin-web.catalog.products._form', [
            'product' => $product,
            'formAction' => route('admin-web.products.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Tạo sản phẩm',
        ])
    </div>
@endsection
