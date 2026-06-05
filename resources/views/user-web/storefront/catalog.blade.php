@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Cửa hàng - Heritage Harvest')

@section('content')
@php
    $query = request()->query();
    $gridUrl = route('user-web.products.index', array_merge($query, ['view' => 'grid']));
    $listUrl = route('user-web.products.index', array_merge($query, ['view' => 'list']));
    $activeView = $catalogView ?? 'grid';
    $priceValue = (int) ($filters['priceLimit'] ?? 30000000);
@endphp

<section class="mx-auto max-w-7xl px-6 pb-20 pt-24">
    <nav class="mb-8 flex items-center space-x-2 text-sm text-on-surface-variant">
        <a class="transition-colors hover:text-primary" href="{{ route('user-web.home') }}">Trang chủ</a>
        @include('user-web.partials.icon', ['name' => 'chevron_right', 'class' => 'text-sm'])
        <a class="transition-colors hover:text-primary" href="{{ route('user-web.products.index') }}">Cửa hàng</a>
        @include('user-web.partials.icon', ['name' => 'chevron_right', 'class' => 'text-sm'])
        <span class="font-medium text-primary">Danh mục</span>
    </nav>

    <div class="flex flex-col gap-12 lg:flex-row">
        <aside class="w-full space-y-10 lg:w-1/4">
            <form class="space-y-10" action="{{ route('user-web.products.index') }}" method="GET">
                <input type="hidden" name="view" value="{{ $activeView }}">

                <div class="space-y-3">
                    <h3 class="font-headline text-lg font-semibold tracking-tight text-on-surface">Tìm kiếm</h3>
                    <div class="relative">
                        <input class="w-full rounded-xl border-b-2 border-transparent bg-surface-container-highest px-4 py-3 pr-12 text-sm outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" name="search" value="{{ $filters['search'] }}" placeholder="Tìm tên sản phẩm...">
                        @include('user-web.partials.icon', ['name' => 'search', 'class' => 'absolute right-3 top-3 text-outline'])
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="border-b border-outline-variant/30 pb-2 font-headline text-lg font-semibold tracking-tight text-on-surface">Danh mục</h3>
                    <div class="flex flex-col space-y-3">
                        @foreach($categories as $category)
                            <label class="group flex cursor-pointer items-center">
                                <input class="h-5 w-5 rounded border-outline-variant text-primary focus:ring-primary" type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $filters['categoryIds'], true))>
                                <span class="ml-3 text-on-surface-variant transition-colors group-hover:text-primary">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="border-b border-outline-variant/30 pb-2 font-headline text-lg font-semibold tracking-tight text-on-surface">Nhà cung cấp</h3>
                    <div class="flex flex-col space-y-3">
                        @foreach($suppliers as $supplier)
                            <label class="group flex cursor-pointer items-center">
                                <input class="h-5 w-5 rounded border-outline-variant text-primary focus:ring-primary" type="checkbox" name="suppliers[]" value="{{ $supplier->id }}" @checked(in_array($supplier->id, $filters['supplierIds'], true))>
                                <span class="ml-3 text-on-surface-variant transition-colors group-hover:text-primary">{{ $supplier->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if($regions->isNotEmpty())
                    <div class="space-y-4">
                        <h3 class="border-b border-outline-variant/30 pb-2 font-headline text-lg font-semibold tracking-tight text-on-surface">Vùng miền</h3>
                        <div class="flex flex-col space-y-3">
                            @foreach($regions as $region)
                                <label class="group flex cursor-pointer items-center">
                                    <input class="h-5 w-5 rounded border-outline-variant text-primary focus:ring-primary" type="checkbox" name="regions[]" value="{{ $region->id }}" @checked(in_array($region->id, $filters['regionIds'], true))>
                                    <span class="ml-3 text-on-surface-variant transition-colors group-hover:text-primary">{{ $region->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="space-y-4">
                    <h3 class="border-b border-outline-variant/30 pb-2 font-headline text-lg font-semibold tracking-tight text-on-surface">Khoảng giá</h3>
                    <div class="px-2">
                        <input class="h-1 w-full cursor-pointer appearance-none rounded-lg bg-surface-container-highest accent-primary" type="range" name="price" min="0" max="30000000" step="10000" value="{{ $priceValue }}" data-range-output="#catalog-price-output">
                        <div class="mt-3 flex justify-between text-xs uppercase tracking-wider text-outline">
                            <span>0đ</span>
                            <span id="catalog-price-output">{{ number_format($priceValue, 0, ',', '.') }}đ</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="border-b border-outline-variant/30 pb-2 font-headline text-lg font-semibold tracking-tight text-on-surface">Đánh giá</h3>
                    <label class="flex cursor-pointer items-center">
                        <input class="sr-only" type="checkbox" name="ratingMin" value="4" @checked((float) $filters['ratingMin'] >= 4)>
                        <span class="flex text-tertiary">
                            @for($star = 0; $star < 4; $star++)
                                @include('user-web.partials.icon', ['name' => 'star', 'class' => 'text-sm', 'fill' => true])
                            @endfor
                            @include('user-web.partials.icon', ['name' => 'star', 'class' => 'text-sm text-outline-variant'])
                        </span>
                        <span class="ml-2 text-sm text-on-surface-variant transition-colors hover:text-primary">Từ 4 sao trở lên</span>
                        @if((float) $filters['ratingMin'] >= 4)
                            <span class="ml-2 rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-primary">On</span>
                        @endif
                    </label>
                </div>

                <div class="grid gap-3">
                    <button class="w-full rounded-xl bg-primary px-4 py-3 font-medium text-on-primary transition-colors hover:opacity-90 active:scale-[0.98]" type="submit">Áp dụng bộ lọc</button>
                    <a class="w-full rounded-xl border border-outline-variant px-4 py-3 text-center font-medium text-on-surface-variant transition-colors hover:bg-surface-container-low active:scale-[0.98]" href="{{ route('user-web.products.index', ['view' => $activeView]) }}">Xóa tất cả bộ lọc</a>
                </div>
            </form>
        </aside>

        <section class="w-full lg:w-3/4">
            <div class="mb-10 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="font-headline text-3xl font-bold tracking-tight text-on-surface">Sản phẩm đặc sắc</h1>
                    <p class="mt-1 text-sm text-on-surface-variant">{{ $products->total() }} sản phẩm phù hợp bộ lọc</p>
                </div>

                <div class="flex w-full items-center space-x-4 sm:w-auto">
                    <form class="relative min-w-[180px]" action="{{ route('user-web.products.index') }}" method="GET">
                        @foreach(request()->except(['sort', 'page']) as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <select class="w-full appearance-none rounded-full border-none bg-surface-container-low px-5 py-2.5 text-sm font-medium focus:ring-2 focus:ring-primary/20" name="sort" onchange="this.form.submit()">
                            @foreach(['popular' => 'Phổ biến nhất', 'newest' => 'Mới nhất', 'price-asc' => 'Giá: Thấp đến Cao', 'price-desc' => 'Giá: Cao đến Thấp'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @include('user-web.partials.icon', ['name' => 'expand_more', 'class' => 'pointer-events-none absolute right-4 top-2.5 text-lg text-outline'])
                    </form>

                    <div class="flex rounded-full bg-surface-container-low p-1">
                        <a class="rounded-full p-2 {{ $activeView === 'grid' ? 'bg-surface-container-lowest text-primary shadow-sm' : 'text-outline hover:text-on-surface' }}" href="{{ $gridUrl }}" aria-label="Chế độ lưới">
                            @include('user-web.partials.icon', ['name' => 'grid_view'])
                        </a>
                        <a class="rounded-full p-2 {{ $activeView === 'list' ? 'bg-surface-container-lowest text-primary shadow-sm' : 'text-outline hover:text-on-surface' }}" href="{{ $listUrl }}" aria-label="Chế độ danh sách">
                            @include('user-web.partials.icon', ['name' => 'view_list'])
                        </a>
                    </div>
                </div>
            </div>

            @if($products->isEmpty())
                <div class="rounded-xl bg-surface-container-lowest p-10 text-center">
                    <h2 class="font-headline text-2xl font-semibold">Chưa có sản phẩm phù hợp</h2>
                    <p class="mt-3 text-on-surface-variant">Hãy thử nới rộng bộ lọc hoặc tải lại trang.</p>
                </div>
            @elseif($activeView === 'grid')
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($products as $product)
                        @include('user-web.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div class="space-y-6">
                    @foreach($products as $product)
                        @php
                            $image = trim((string) $product->image_url);
                            $isAvailable = $product->is_active && ! $product->is_deleted && $product->stock_quantity > 0;
                        @endphp
                        <article class="flex flex-col gap-6 rounded-xl bg-surface-container-lowest p-5 md:flex-row">
                            <a class="md:w-56" href="{{ $ui->productUrl($product) }}">
                                @if($image !== '')
                                    <img class="aspect-[4/5] w-full rounded-xl object-cover" src="{{ $image }}" alt="{{ $product->name }}">
                                @else
                                    <div class="flex aspect-[4/5] w-full items-center justify-center rounded-xl bg-surface-container-low text-primary">
                                        @include('user-web.partials.icon', ['name' => 'inventory_2', 'class' => 'text-5xl'])
                                    </div>
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col justify-between gap-4">
                                <div>
                                    <div class="mb-3 flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-primary">{{ $product->region?->name ?? 'Đặc sản' }}</span>
                                        <span class="text-sm text-on-surface-variant">{{ $product->category?->name ?? $product->supplier?->name }}</span>
                                    </div>
                                    <a class="font-headline text-2xl font-semibold" href="{{ $ui->productUrl($product) }}">{{ $product->name }}</a>
                                    <p class="mt-3 max-w-2xl text-sm leading-7 text-on-surface-variant">{{ $product->description ?: $product->short_description }}</p>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <span class="text-xl font-bold text-on-surface">{{ $ui->money($product->sale_price) }}</span>
                                        <span class="text-sm text-on-surface-variant">4.8 / 5</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <a class="rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-medium text-on-surface-variant transition-colors hover:text-primary" href="{{ $ui->productUrl($product) }}">Xem chi tiết</a>
                                        <form action="{{ route('user-web.cart.items.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-on-primary disabled:cursor-not-allowed disabled:bg-surface-container-high disabled:text-on-surface-variant" type="submit" @disabled(! $isAvailable)>Thêm vào giỏ</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            @include('user-web.partials.pagination', ['paginator' => $products])
        </section>
    </div>
</section>
@endsection
