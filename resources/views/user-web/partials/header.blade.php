@inject('ui', 'App\Support\UserWeb\UserWebPresenter')
@php
    $path = request()->path();
    $variant = request()->is('/') ? 'home' : (request()->is('products') ? 'catalog' : (request()->is('products/*') ? 'detail' : (request()->is('checkout*') ? 'checkout' : 'default')));
    $accountTarget = $ui->redirectForUser($currentUser ?? null);
    $canUseStorefrontActions = ! $currentUser || $currentUser->role === \App\Models\User::ROLE_CUSTOMER;
    $navItems = $variant === 'catalog'
        ? [
            ['label' => 'Cửa hàng', 'url' => route('user-web.products.index')],
            ['label' => 'Bài viết', 'url' => route('user-web.story')],
            ['label' => 'Vùng miền', 'url' => route('user-web.regions')],
            ['label' => 'Đăng nhập', 'url' => route('user-web.login')],
        ]
        : [
            ['label' => 'Cửa hàng', 'url' => route('user-web.products.index')],
            ['label' => 'Bài viết', 'url' => route('user-web.story')],
            ['label' => 'Vùng miền', 'url' => route('user-web.regions')],
            // ['label' => 'Test Sản Phẩm', 'url' => route('user-web.product-test')],
        ];
@endphp
<nav class="fixed top-0 z-50 w-full bg-white/80 shadow-sm backdrop-blur-xl">
    <div class="mx-auto flex w-full items-center justify-between px-6 py-4 tracking-tight {{ in_array($variant, ['home', 'detail', 'checkout'], true) ? 'max-w-screen-2xl' : 'max-w-7xl' }}">
        <a href="{{ route('user-web.home') }}" class="text-xl font-bold tracking-tighter text-green-900">
            Heritage Harvest
        </a>

        <div class="hidden items-center gap-8 md:flex">
            @foreach($navItems as $item)
                <a href="{{ $item['url'] }}" class="pb-1 text-sm font-medium transition-colors {{ request()->fullUrlIs($item['url']) || request()->url() === $item['url'] ? 'border-b-2 border-primary text-primary' : 'text-zinc-500 hover:text-green-800' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            @if($variant === 'home')
                <form class="hidden items-center rounded-full bg-surface-container-low px-3 py-1.5 sm:flex" action="{{ route('user-web.products.index') }}" method="GET">
                    @include('user-web.partials.icon', ['name' => 'search', 'class' => 'text-lg text-on-surface-variant'])
                    <input class="w-40 border-none bg-transparent text-sm outline-none placeholder:text-on-surface-variant" placeholder="Tìm kiếm sản phẩm..." name="search">
                </form>
            @elseif($variant === 'catalog')
                <a class="text-zinc-600 transition hover:text-primary" href="{{ route('user-web.products.index') }}" aria-label="Tìm kiếm sản phẩm">
                    @include('user-web.partials.icon', ['name' => 'search'])
                </a>
            @endif

            @if($canUseStorefrontActions)
                <a href="{{ route('user-web.checkout.show') }}" class="relative rounded-full p-2 text-zinc-600 transition hover:bg-zinc-50 hover:text-primary" aria-label="Giỏ hàng">
                    @include('user-web.partials.icon', ['name' => 'shopping_cart'])
                    @if(($cartCount ?? 0) > 0)
                        <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-tertiary text-[10px] font-bold text-on-tertiary">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
            @endif

            <a href="{{ $accountTarget }}" class="rounded-full p-2 text-zinc-600 transition hover:bg-zinc-50 hover:text-primary {{ request()->is('account*') ? 'text-green-800' : '' }}" aria-label="{{ $currentUser ? 'Khu vực tài khoản' : 'Đăng nhập' }}">
                @include('user-web.partials.icon', ['name' => 'person'])
            </a>

            @if($currentUser)
                <a href="{{ route('user-web.logout') }}" class="rounded-full p-2 text-zinc-600 transition hover:bg-zinc-50 hover:text-primary" aria-label="Đăng xuất">
                    @include('user-web.partials.icon', ['name' => 'logout'])
                </a>
            @endif

            <button class="rounded-full p-2 text-zinc-600 md:hidden" type="button" data-toggle="#mobile-menu" aria-label="Menu">
                @include('user-web.partials.icon', ['name' => 'menu'])
            </button>
        </div>
    </div>
    <div id="mobile-menu" class="hidden border-t border-zinc-100 bg-white px-6 py-4 md:hidden">
        <div class="flex flex-col gap-3">
            @foreach($navItems as $item)
                <a href="{{ $item['url'] }}" class="rounded-full px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-surface-container-low hover:text-primary">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</nav>
