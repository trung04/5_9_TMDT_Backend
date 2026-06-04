@extends('admin-web.layouts.app')

@section('title', 'Sửa nhà cung cấp')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Sửa nhà cung cấp</h2>
            <p>{{ $supplier->name }} ({{ $supplier->supplier_code }})</p>
        </div>
        <div class="toolbar-actions">
            @if($adminUser->hasAdminPermission('admin.suppliers.delete'))
                <form method="POST" action="{{ route('admin-web.suppliers.destroy', $supplier->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Xóa</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        @include('admin-web.catalog.suppliers._form', [
            'supplier' => $supplier,
            'formAction' => route('admin-web.suppliers.update', $supplier->id),
            'formMethod' => 'PUT',
            'submitLabel' => 'Lưu thay đổi',
        ])
    </div>
@endsection
