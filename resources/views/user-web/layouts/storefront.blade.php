@inject('ui', 'App\Support\UserWeb\UserWebPresenter')
@php
    /** @var \App\Models\User|null $currentUser */
    $currentUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
    $activeCart = $currentUser?->role === \App\Models\User::ROLE_CUSTOMER
        ? $currentUser->activeCart()->with('items')->first()
        : null;
    $cartCount = $currentUser?->role === \App\Models\User::ROLE_CUSTOMER
        ? $ui->cartQuantity($activeCart)
        : app(\App\Services\GuestCartService::class)->totalQuantity();
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Heritage Harvest')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    @if(! app()->environment('testing'))
        @vite(['resources/css/user-web.css', 'resources/js/user-web.js'])
    @endif
</head>
<body class="min-h-screen bg-surface text-on-surface font-body">
<div class="min-h-screen bg-surface text-on-surface">
    @include('user-web.partials.header', ['currentUser' => $currentUser, 'cartCount' => $cartCount])
    <main>
        @include('user-web.partials.alerts')
        @yield('content')
    </main>
    @include('user-web.partials.footer')
</div>
</body>
</html>
