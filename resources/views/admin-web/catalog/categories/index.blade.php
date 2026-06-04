@extends('admin-web.layouts.app')

@section('title', 'Danh mục')

@section('content')
    @php($labels = \App\Support\AdminWebLabel::class)

    <div class="toolbar">
        <div>
            <h2>Danh mục</h2>
            <p>Quản lý bản ghi danh mục ngay trong giao diện quản trị Laravel.</p>
        </div>
        @if($adminUser->hasAdminPermission('admin.categories.create'))
            <a class="btn btn-primary" href="{{ route('admin-web.categories.create') }}">Tạo danh mục</a>
        @endif
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Tên</th>
                    <th>Sản phẩm</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->products_count }}</td>
                        <td>{{ $labels::active((bool) $category->is_active) }}</td>
                        <td>
                            <div class="row">
                                @if($adminUser->hasAdminPermission('admin.categories.update'))
                                    <a class="btn btn-secondary" href="{{ route('admin-web.categories.edit', $category->id) }}">Sửa</a>
                                @endif
                                @if($adminUser->hasAdminPermission('admin.categories.delete'))
                                    <form method="POST" action="{{ route('admin-web.categories.destroy', $category->id) }}">
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
        @include('admin-web.partials.pagination', ['paginator' => $categories])
    </div>
@endsection
