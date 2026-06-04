@extends('admin-web.layouts.app')

@section('title', 'Nhà cung cấp')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Nhà cung cấp</h2>
            <p>Quản lý danh bạ nhà cung cấp cùng phần còn lại của trang quản trị dựng sẵn.</p>
        </div>
        @if($adminUser->hasAdminPermission('admin.suppliers.create'))
            <a class="btn btn-primary" href="{{ route('admin-web.suppliers.create') }}">Tạo nhà cung cấp</a>
        @endif
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên</th>
                    <th>Sản phẩm</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->supplier_code }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->products_count }}</td>
                        <td>
                            <div class="row">
                                @if($adminUser->hasAdminPermission('admin.suppliers.update'))
                                    <a class="btn btn-secondary" href="{{ route('admin-web.suppliers.edit', $supplier->id) }}">Sửa</a>
                                @endif
                                @if($adminUser->hasAdminPermission('admin.suppliers.delete'))
                                    <form method="POST" action="{{ route('admin-web.suppliers.destroy', $supplier->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">Xóa</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('admin-web.partials.pagination', ['paginator' => $suppliers])
    </div>
@endsection
