@inject('ui', 'App\Support\UserWeb\UserWebPresenter')
@php
    /** @var \App\Models\User|null $currentUser */
    $currentUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
    $variant = $variant ?? (request()->is('supplier*') ? 'supplier' : 'warehouse');
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Operations Portal') - Heritage Harvest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    @if(! app()->environment('testing'))
        @vite(['resources/css/user-web.css', 'resources/js/user-web.js'])
    @endif
</head>
<body class="min-h-screen bg-surface text-on-surface font-body">
<div class="min-h-screen bg-surface">
    <div id="portal-mobile-sidebar" class="hidden fixed inset-0 z-50 lg:hidden">
        <button class="absolute inset-0 bg-on-surface/30" type="button" data-toggle="#portal-mobile-sidebar" aria-label="Đóng dieu huong"></button>
        <div class="relative h-full w-64">
            @include('user-web.partials.portal-sidebar', ['variant' => $variant])
        </div>
    </div>

    <div class="hidden fixed inset-y-0 left-0 z-40 w-64 lg:block">
        @include('user-web.partials.portal-sidebar', ['variant' => $variant])
    </div>

    <div class="sticky top-0 z-30 flex items-center justify-between bg-surface/80 px-4 py-4 backdrop-blur-xl lg:hidden">
        <div>
            <p class="text-sm uppercase tracking-widest text-on-surface-variant">
                {{ $variant === 'supplier' ? 'Nhà cung cấp' : 'Kho vận' }}
            </p>
            <h1 class="font-headline text-xl font-bold text-primary">Heritage Harvest</h1>
        </div>
        <button class="rounded-full bg-surface-container-low p-2 text-on-surface-variant" type="button" data-toggle="#portal-mobile-sidebar" aria-label="Mo dieu huong">
            @include('user-web.partials.icon', ['name' => 'menu', 'class' => 'text-2xl'])
        </button>
    </div>

    <main class="min-h-screen px-4 py-4 lg:ml-64 lg:px-8 lg:py-8">
        @include('user-web.partials.alerts')
        @yield('content')
    </main>
</div>
</body>
</html>
