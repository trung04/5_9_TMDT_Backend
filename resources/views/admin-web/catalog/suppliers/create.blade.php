@extends('admin-web.layouts.app')

@section('title', 'Tạo nhà cung cấp')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Tạo nhà cung cấp</h2>
            <p>Thêm nhà cung cấp trên trang riêng thay vì trộn vào danh sách.</p>
        </div>
    </div>

    <div class="card">
        @include('admin-web.catalog.suppliers._form', [
            'supplier' => $supplier,
            'formAction' => route('admin-web.suppliers.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Tạo nhà cung cấp',
        ])
    </div>
@endsection
