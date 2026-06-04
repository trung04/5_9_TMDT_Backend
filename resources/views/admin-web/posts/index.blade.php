@extends('admin-web.layouts.app')

@section('title', 'Bài viết')

@section('content')
    @php($canCreatePosts = $adminUser->hasAdminPermission('admin.community.posts.create'))
    @php($canUpdatePosts = $adminUser->hasAdminPermission('admin.community.posts.update'))
    @php($canModerateComments = $adminUser->hasAdminPermission('admin.community.comments.moderate'))
    @php($canDeletePosts = $adminUser->hasAdminPermission('admin.community.posts.delete'))
    @php($labels = \App\Support\AdminWebLabel::class)

    <div class="toolbar">
        <div>
            <h2>Bài viết</h2>
            <p>Kiểm duyệt nội dung cộng đồng trực tiếp trên Laravel mà không cần điều phối phía client.</p>
        </div>
        <div class="toolbar-actions">
            @if($adminUser->hasAdminPermission('admin.community.view'))
                <a class="btn btn-secondary" href="{{ route('admin-web.community.index') }}">Quay lại cộng đồng</a>
            @endif
            @if($canCreatePosts)
                <a class="btn btn-primary" href="{{ route('admin-web.posts.create') }}">Tạo bài viết</a>
            @endif
        </div>
    </div>

    <div class="card">
        <h3>Danh sách bài viết</h3>
        @if($posts->isEmpty())
            <div class="empty">Chưa có bài viết nào.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Trạng thái</th>
                        <th>Xuất bản</th>
                        <th>Thống kê</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                        <tr>
                            <td>
                                <strong>{{ $post->title }}</strong><br>
                                <span class="muted small">{{ $post->author?->full_name ?? 'Không rõ tác giả' }}</span>
                            </td>
                            <td>{{ $labels::postStatus($post->status) }}</td>
                            <td>{{ optional($post->published_at)->format('Y-m-d H:i') ?? 'Bản nháp' }}</td>
                            <td>{{ $post->likes_count }} lượt thích / {{ $post->comments_count }} bình luận</td>
                            <td>
                                <div class="row">
                                    @if($canUpdatePosts || $canModerateComments)
                                        <a class="btn btn-secondary" href="{{ route('admin-web.posts.edit', $post->id) }}">
                                            {{ $canUpdatePosts ? ($canModerateComments ? 'Sửa và duyệt' : 'Sửa') : 'Duyệt' }}
                                        </a>
                                    @endif
                                    @if($canDeletePosts)
                                        <form method="POST" action="{{ route('admin-web.posts.destroy', $post->id) }}">
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
            @include('admin-web.partials.pagination', ['paginator' => $posts])
        @endif
    </div>
@endsection
