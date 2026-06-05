@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Bài viết - Heritage Harvest')

@section('content')
<section class="px-6 pb-16 pt-28">
    <div class="mx-auto max-w-5xl space-y-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-primary">Community stories</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight text-green-950">Bài viết cộng đồng</h1>
        </div>

        @forelse($posts as $post)
            @php($liked = in_array($post->id, $likedPostIds, true))
            <article class="overflow-hidden rounded-[2rem] bg-white shadow-ambient">
                @if($post->cover_image_url)
                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="h-64 w-full object-cover">
                @else
                    <div class="flex h-44 w-full items-center justify-center bg-surface-container-low text-primary">
                        @include('user-web.partials.icon', ['name' => 'article', 'class' => 'text-5xl'])
                    </div>
                @endif
                <div class="space-y-6 p-6">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-on-surface-variant">
                        <span>{{ $ui->date($post->published_at) }}</span>
                        <span>{{ $post->author?->full_name ?? 'Admin' }}</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-green-950">{{ $post->title }}</h2>
                        @if($post->excerpt)
                            <p class="mt-3 text-sm leading-6 text-on-surface-variant">{{ $post->excerpt }}</p>
                        @endif
                    </div>
                    <div class="space-y-3 text-sm leading-7 text-on-surface-variant">
                        @foreach(preg_split('/\r?\n/', (string) $post->body) ?: [] as $line)
                            @if(trim($line) !== '')
                                <p>{{ $line }}</p>
                            @endif
                        @endforeach
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <form action="{{ route('user-web.story.likes', $post) }}" method="POST">
                            @csrf
                            <button class="inline-flex items-center gap-2 rounded-full {{ $liked ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-primary' }} px-4 py-2 text-sm font-semibold" type="submit">
                                @include('user-web.partials.icon', ['name' => 'favorite', 'fill' => $liked])
                                {{ $post->likes_count }} thich
                            </button>
                        </form>
                        <span class="rounded-full bg-surface-container-low px-4 py-2 text-sm font-semibold text-on-surface-variant">{{ $post->comments_count }} bình luận</span>
                    </div>
                    <div class="space-y-3 rounded-[2rem] bg-surface-container-low p-4">
                        @forelse($post->visibleComments as $comment)
                            <div class="rounded-2xl bg-white p-4 text-sm">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <p class="font-semibold">{{ $comment->author?->full_name ?? 'Khách hàng' }}</p>
                                    <span class="text-xs text-on-surface-variant">{{ $ui->date($comment->created_at) }}</span>
                                </div>
                                <p class="text-on-surface-variant">{{ $comment->content }}</p>
                            </div>
                        @empty
                            <p class="rounded-2xl bg-white p-4 text-sm text-on-surface-variant">Chưa có bình luận.</p>
                        @endforelse
                        <form class="flex flex-col gap-3 sm:flex-row" action="{{ route('user-web.story.comments', $post) }}" method="POST">
                            @csrf
                            <input class="min-h-12 flex-1 rounded-full border-none bg-white px-5 text-sm outline-none focus:ring-2 focus:ring-primary/20" name="content" placeholder="Viet bình luận...">
                            <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-on-primary" type="submit">Gửi</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-[2rem] bg-surface-container-low p-8 text-sm text-on-surface-variant">Chưa có bài viết.</div>
        @endforelse

        @include('user-web.partials.pagination', ['paginator' => $posts])
    </div>
</section>
@endsection
