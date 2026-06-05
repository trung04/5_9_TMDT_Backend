<header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h1 class="font-headline text-3xl font-bold tracking-tight text-on-surface md:text-4xl">{{ $title }}</h1>
        @isset($description)
            <p class="mt-3 max-w-3xl text-sm leading-6 text-on-surface-variant">{{ $description }}</p>
        @endisset
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-3">
            {!! $actions !!}
        </div>
    @endisset
</header>
