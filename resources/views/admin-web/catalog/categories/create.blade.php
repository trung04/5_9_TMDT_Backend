@extends('admin-web.layouts.app')

@section('title', 'Tạo danh mục')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Tạo danh mục</h2>
            <p>Thêm danh mục mới trên một trang biểu mẫu riêng.</p>
        </div>
    </div>

    <div class="card">
        @include('admin-web.catalog.categories._form', [
            'category' => $category,
            'formAction' => route('admin-web.categories.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Tạo danh mục',
        ])
    </div>
@endsection
