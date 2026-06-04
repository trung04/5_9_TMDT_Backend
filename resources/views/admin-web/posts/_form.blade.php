@php($labels = \App\Support\AdminWebLabel::class)

<form method="POST" action="{{ $formAction }}" class="form-grid">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif
    <label class="full">
        Tiêu đề
        <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required>
    </label>
    <label class="full">
        Tóm tắt
        <textarea name="excerpt">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
    </label>
    <label class="full">
        Nội dung
        <textarea name="body" required>{{ old('body', $post->body ?? '') }}</textarea>
    </label>
    <label class="full">
        URL ảnh bìa
        <input type="url" name="cover_image_url" value="{{ old('cover_image_url', $post->cover_image_url ?? '') }}">
    </label>
    <label>
        Trạng thái
        <select name="status">
            @foreach([\App\Models\Post::STATUS_DRAFT, \App\Models\Post::STATUS_PUBLISHED] as $status)
                <option value="{{ $status }}" @selected(old('status', $post->status ?? \App\Models\Post::STATUS_DRAFT) === $status)>{{ $labels::postStatus($status) }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Thời điểm xuất bản
        <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at ?? null)->format('Y-m-d\TH:i')) }}">
    </label>
    <div class="full row">
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
        @if($adminUser->hasAdminPermission('admin.community.posts.view'))
            <a class="btn btn-secondary" href="{{ route('admin-web.posts.index') }}">Quay lại bài viết</a>
        @endif
    </div>
</form>
