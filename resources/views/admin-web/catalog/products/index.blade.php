@extends('admin-web.layouts.app')

@section('title', 'Sản phẩm')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Sản phẩm</h2>
            <p>Quản lý sản phẩm phía máy chủ với cùng bộ trường đang dùng cho API.</p>
        </div>
        @if($adminUser->hasAdminPermission('admin.products.create'))
            <div class="toolbar-actions">
                <a class="btn btn-primary" href="{{ route('admin-web.products.create') }}">Tạo sản phẩm</a>
            </div>
        @endif
    </div>

    <div class="card">
        <h3>Danh sách sản phẩm</h3>
        <form class="filters" method="GET" action="{{ route('admin-web.products.index') }}">
            <label>
                Từ khóa
                <input type="text" name="keyword" value="{{ request('keyword') }}">
            </label>
            <label>
                Danh mục
                <select name="category_id">
                    <option value="">Tất cả</option>
                    @foreach($categoriesForForm as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Khu vực
                <select name="region_id">
                    <option value="">Tất cả</option>
                    @foreach($regionsForForm as $region)
                        <option value="{{ $region->id }}" @selected((string) request('region_id') === (string) $region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-secondary" type="submit">Lọc</button>
        </form>

        @if($products->isEmpty())
            <div class="empty">Không tìm thấy sản phẩm nào.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Tên</th>
                        <th>Danh mục</th>
                        <th>Tồn kho</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->stock_quantity }}</td>
                            <td>
                                <div class="row">
                                    @if($adminUser->hasAdminPermission('admin.products.update'))
                                        <a class="btn btn-secondary" href="{{ route('admin-web.products.edit', $product->id) }}">Sửa</a>
                                    @endif
                                    @if($adminUser->hasAdminPermission('admin.products.delete'))
                                        <form method="POST" action="{{ route('admin-web.products.destroy', $product->id) }}">
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
            @include('admin-web.partials.pagination', ['paginator' => $products])
        @endif
    </div>
@endsection
