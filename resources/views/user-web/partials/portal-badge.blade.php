@php
    $toneClass = [
        'success' => 'bg-primary/10 text-primary',
        'warning' => 'bg-tertiary/10 text-tertiary',
        'danger' => 'bg-error/10 text-error',
        'primary' => 'bg-primary/10 text-primary',
        'neutral' => 'bg-surface-container-low text-on-surface-variant',
    ][$tone ?? 'neutral'] ?? 'bg-surface-container-low text-on-surface-variant';
@endphp
<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-widest {{ $toneClass }}">
    {{ $slot ?? $label ?? '' }}
</span>
