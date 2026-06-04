@extends('admin-web.layouts.app')

@section('title', 'Sửa bài viết')

@section('content')
    @php($canUpdatePost = $adminUser->hasAdminPermission('admin.community.posts.update'))
    @php($canModerateComments = $adminUser->hasAdminPermission('admin.community.comments.moderate'))
    @php($labels = \App\Support\AdminWebLabel::class)

    <div class="toolbar">
        <div>
            <h2>Sửa bài viết</h2>
            <p>{{ $post->title }}</p>
        </div>
        <div class="toolbar-actions">
            @if($adminUser->hasAdminPermission('admin.community.posts.view'))
                <a class="btn btn-secondary" href="{{ route('admin-web.posts.index') }}">Quay lại bài viết</a>
            @endif
            @if($adminUser->hasAdminPermission('admin.community.posts.delete'))
                <form method="POST" action="{{ route('admin-web.posts.destroy', $post->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Xóa</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h3>{{ $canUpdatePost ? 'Thông tin bài viết' : 'Chi tiết bài viết' }}</h3>
            @if($canUpdatePost)
                @include('admin-web.posts._form', [
                    'post' => $post,
                    'formAction' => route('admin-web.posts.update', $post->id),
                    'formMethod' => 'PUT',
                    'submitLabel' => 'Lưu thay đổi',
                ])
            @else
                <div class="stack">
                    <div class="metric">
                        <strong>Trạng thái</strong>
                        <div class="value" style="font-size: 18px;">{{ $labels::postStatus($post->status) }}</div>
                        <div class="small muted">
                            Tác giả: {{ $post->author?->full_name ?? 'Không rõ tác giả' }}
                        </div>
                        <div class="small muted">
                            Xuất bản: {{ optional($post->published_at)->format('Y-m-d H:i') ?? 'Bản nháp' }}
                        </div>
                    </div>
                    @if($post->excerpt)
                        <div class="metric">
                            <strong>Tóm tắt</strong>
                            <div class="small muted" style="margin-top: 10px;">{{ $post->excerpt }}</div>
                        </div>
                    @endif
                    <div class="metric">
                        <strong>Nội dung</strong>
                        <div class="small muted" style="margin-top: 10px;">{!! nl2br(e($post->body)) !!}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="card">
            <h3>Tương tác</h3>
            <div class="grid">
                <div class="metric">
                    <strong>Lượt thích</strong>
                    <div class="value">{{ $post->likes_count }}</div>
                </div>
                <div class="metric">
                    <strong>Bình luận</strong>
                    <div class="value">{{ $post->comments_count }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($canModerateComments)
        <div class="card">
            <h3>Kiểm duyệt bình luận</h3>
            @if($post->comments->isEmpty())
                <div class="empty">Chưa có bình luận nào cần duyệt cho bài viết này.</div>
            @else
                <div class="stack">
                    @foreach($post->comments as $comment)
                        <div class="metric">
                            <div class="row between">
                                <div>
                                    <strong>{{ $comment->author?->full_name ?? 'Không rõ người dùng' }}</strong>
                                    <div class="small muted">{{ $comment->author?->email }}</div>
                                </div>
                                <form class="inline-form" method="POST" action="{{ route('admin-web.posts.comments.visibility', $comment->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status">
                                        @foreach([\App\Models\PostComment::STATUS_VISIBLE, \App\Models\PostComment::STATUS_HIDDEN] as $status)
                                            <option value="{{ $status }}" @selected($comment->status === $status)>{{ $labels::commentStatus($status) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-secondary" type="submit">Áp dụng</button>
                                </form>
                            </div>
                            <div class="small muted" style="margin-top: 10px;">{{ $comment->content }}</div>
                            @if($comment->hiddenBy)
                                <div class="small muted" style="margin-top: 8px;">
                                    Ẩn bởi {{ $comment->hiddenBy->full_name }} lúc {{ optional($comment->hidden_at)->format('Y-m-d H:i') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endsection
