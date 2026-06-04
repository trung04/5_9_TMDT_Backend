@extends('admin-web.layouts.app')

@section('title', 'Sửa sản phẩm')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Sửa sản phẩm</h2>
            <p>{{ $product->name }} ({{ $product->sku }})</p>
        </div>
        <div class="toolbar-actions">
            @if($adminUser->hasAdminPermission('admin.products.delete'))
                <form method="POST" action="{{ route('admin-web.products.destroy', $product->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Xóa</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        @include('admin-web.catalog.products._form', [
            'product' => $product,
            'formAction' => route('admin-web.products.update', $product->id),
            'formMethod' => 'PUT',
            'submitLabel' => 'Lưu thay đổi',
        ])
    </div>
@endsection
