@extends('admin-web.layouts.app')

@section('title', 'Tạo bài viết')

@section('content')
    <div class="toolbar">
        <div>
            <h2>Tạo bài viết</h2>
            <p>Soạn và xuất bản nội dung cộng đồng trên một trang dựng sẵn riêng.</p>
        </div>
        <div class="toolbar-actions">
            @if($adminUser->hasAdminPermission('admin.community.posts.view'))
                <a class="btn btn-secondary" href="{{ route('admin-web.posts.index') }}">Quay lại bài viết</a>
            @endif
        </div>
    </div>

    <div class="card">
        @include('admin-web.posts._form', [
            'post' => $post,
            'formAction' => route('admin-web.posts.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Tạo bài viết',
        ])
    </div>
@endsection
