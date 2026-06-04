@extends('admin-web.layouts.app')

@section('title', 'Sửa danh mục')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Sửa danh mục</h2>
            <p>{{ $category->name }}</p>
        </div>
        <div class="toolbar-actions">
            @if($adminUser->hasAdminPermission('admin.categories.delete'))
                <form method="POST" action="{{ route('admin-web.categories.destroy', $category->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Xóa</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        @include('admin-web.catalog.categories._form', [
            'category' => $category,
            'formAction' => route('admin-web.categories.update', $category->id),
            'formMethod' => 'PUT',
            'submitLabel' => 'Lưu thay đổi',
        ])
    </div>
@endsection
