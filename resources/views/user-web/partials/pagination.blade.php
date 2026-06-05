@inject('ui', 'App\Support\UserWeb\UserWebPresenter')
@if($paginator->lastPage() > 1)
    <nav class="mt-10 flex items-center justify-center gap-2">
        @foreach($ui->pageWindow($paginator) as $page)
            <a class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold {{ $page === $paginator->currentPage() ? 'bg-primary text-on-primary' : 'bg-white text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}" href="{{ $paginator->url($page) }}">
                {{ $page }}
            </a>
        @endforeach
    </nav>
@endif
