@php
    $toneClass = [
        'primary' => 'bg-primary/10 text-primary',
        'secondary' => 'bg-secondary/10 text-secondary',
        'tertiary' => 'bg-tertiary/10 text-tertiary',
        'danger' => 'bg-error/10 text-error',
        'warning' => 'bg-tertiary/10 text-tertiary',
        'neutral' => 'bg-surface-container-high text-on-surface-variant',
    ][$tone ?? 'primary'] ?? 'bg-primary/10 text-primary';
@endphp
<article class="rounded-[2rem] bg-white p-6 shadow-ambient">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-widest text-on-surface-variant">{{ $label }}</p>
            <p class="mt-3 font-headline text-3xl font-bold">{{ $value }}</p>
        </div>
        <span class="rounded-2xl p-3 {{ $toneClass }}">
            @include('user-web.partials.icon', ['name' => $icon ?? 'insights', 'class' => 'text-2xl'])
        </span>
    </div>
    @isset($delta)
        <p class="mt-4 text-sm font-medium text-primary">{{ $delta }}</p>
    @endisset
    @isset($helper)
        <p class="mt-2 text-xs leading-5 text-on-surface-variant">{{ $helper }}</p>
    @endisset
</article>
