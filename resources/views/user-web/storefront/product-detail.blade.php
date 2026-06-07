@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', $product->name.' - Heritage Harvest')

@section('content')
@php
    $gallery = collect([$product->displayImageUrl()])
        ->merge($product->galleryImageUrls());
    $gallery = $gallery->filter()->unique()->values();
    $activeMedia = $gallery->first();
    $stock = (int) $product->stock_quantity;
    $isOutOfStock = $stock <= 0;
    $maxQuantity = max(1, $stock);
    $lowStockLabel = $stock > 0 && $stock <= 5 ? "Chỉ còn {$stock} sản phẩm" : null;
@endphp

<section class="mx-auto max-w-screen-2xl px-6 pb-16 pt-24">
    <nav class="mb-8 flex items-center space-x-2 text-xs uppercase tracking-widest text-on-surface-variant/60">
        <a class="transition-colors hover:text-primary" href="{{ route('user-web.home') }}">Trang chủ</a>
        @include('user-web.partials.icon', ['name' => 'chevron_right', 'class' => 'text-sm'])
        <a class="transition-colors hover:text-primary" href="{{ route('user-web.products.index') }}">Cửa hàng</a>
        @include('user-web.partials.icon', ['name' => 'chevron_right', 'class' => 'text-sm'])
        <span class="font-semibold text-on-surface">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-12">
        <div class="space-y-4 lg:col-span-7">
            <div class="aspect-[4/3] overflow-hidden rounded-xl bg-surface-container">
                @if($activeMedia)
                    <img id="product-gallery-main" class="h-full w-full object-cover" src="{{ $activeMedia }}" alt="{{ $product->name }}">
                @else
                    <div id="product-gallery-main" class="flex h-full w-full items-center justify-center text-primary">
                        @include('user-web.partials.icon', ['name' => 'inventory_2', 'class' => 'text-7xl'])
                    </div>
                @endif
            </div>

            @if($gallery->count() > 1)
                <div class="grid grid-cols-4 gap-4">
                    @foreach($gallery as $index => $media)
                        <button class="aspect-square overflow-hidden rounded-xl transition-opacity {{ $index === 0 ? 'border-2 border-primary' : 'opacity-70 hover:opacity-100' }}" type="button" data-gallery-thumb data-gallery-main="#product-gallery-main" data-gallery-src="{{ $media }}" data-gallery-alt="Ảnh {{ $index + 1 }} của {{ $product->name }}" aria-label="Xem ảnh {{ $index + 1 }} của {{ $product->name }}">
                            <img class="h-full w-full object-cover" src="{{ $media }}" alt="Ảnh {{ $index + 1 }} của {{ $product->name }}">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-8 lg:col-span-5">
            <header class="space-y-3">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-block rounded-full bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-primary">
                        {{ $product->region?->name ?? $product->category?->name ?? 'Đặc sản' }}
                    </span>
                    <form action="{{ route('user-web.account.wishlist.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button class="inline-flex items-center gap-2 rounded-full border border-outline-variant/20 px-4 py-2 text-sm font-semibold text-on-surface-variant transition-all hover:text-primary" type="submit">
                            @include('user-web.partials.icon', ['name' => 'favorite'])
                            <span>Lưu sản phẩm</span>
                        </button>
                    </form>
                </div>
                <h1 class="font-headline text-3xl font-bold leading-tight tracking-tight text-on-surface">
                    {{ $product->name }}
                </h1>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center text-tertiary">
                        @for($star = 0; $star < 4; $star++)
                            @include('user-web.partials.icon', ['name' => 'star', 'class' => 'text-sm', 'fill' => true])
                        @endfor
                        @include('user-web.partials.icon', ['name' => 'star_half', 'class' => 'text-sm'])
                        <span class="ml-2 text-sm font-medium text-on-surface-variant">4.8 (24 đánh giá)</span>
                    </div>
                </div>
                <p class="pt-2 font-headline text-2xl font-bold text-primary">{{ $ui->money($product->sale_price) }}</p>
            </header>

            <div class="grid grid-cols-2 gap-y-4 border-y border-outline-variant/15 py-6 text-sm">
                <div class="flex flex-col gap-1">
                    <span class="text-xs uppercase tracking-wider text-on-surface-variant">Nhà cung cấp</span>
                    <span class="font-medium">{{ $product->supplier?->name ?? $product->region?->name ?? 'Heritage Harvest' }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs uppercase tracking-wider text-on-surface-variant">SKU / Tag</span>
                    <span class="font-medium">{{ $product->sku ?: ($product->category?->name ?? 'HH') }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs uppercase tracking-wider text-on-surface-variant">Trạng thái</span>
                    <span class="font-medium">{{ $ui->stockLabel($product) }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs uppercase tracking-wider text-on-surface-variant">Danh mục</span>
                    <span class="font-medium">{{ $product->category?->name ?? $product->region?->name ?? 'Đặc sản' }}</span>
                </div>
            </div>

            <form class="space-y-6" action="{{ route('user-web.cart.items.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <p class="text-sm font-medium {{ $isOutOfStock ? 'text-error' : ($lowStockLabel ? 'text-amber-700' : 'text-on-surface-variant') }}">
                    {{ $isOutOfStock ? 'Hết hàng' : ($lowStockLabel ?? "Còn {$stock} sản phẩm") }}
                </p>
                <div class="flex items-center gap-4">
                    <div class="flex items-center rounded-full border border-outline-variant/20 bg-surface-container-low px-4 py-2" data-quantity-control>
                        <button class="flex h-8 w-8 items-center justify-center hover:text-primary" type="button" data-quantity-step="-1" aria-label="Giảm số lượng">
                            @include('user-web.partials.icon', ['name' => 'remove'])
                        </button>
                        <input class="w-12 border-none bg-transparent text-center font-bold outline-none" name="quantity" type="number" min="1" max="{{ $maxQuantity }}" value="1" data-quantity-input>
                        <button class="flex h-8 w-8 items-center justify-center hover:text-primary" type="button" data-quantity-step="1" aria-label="Tăng số lượng">
                            @include('user-web.partials.icon', ['name' => 'add'])
                        </button>
                    </div>
                    <button class="flex-1 rounded-full border border-secondary px-6 py-4 font-bold text-secondary transition-all hover:bg-secondary hover:text-white disabled:cursor-not-allowed disabled:opacity-50" type="submit" @disabled($isOutOfStock)>Thêm vào giỏ</button>
                </div>
                <button class="w-full rounded-full bg-primary-glow px-6 py-4 font-bold text-white shadow-lg shadow-primary/20 transition-transform active:scale-95 disabled:cursor-not-allowed disabled:opacity-50" type="submit" name="redirect_to_checkout" value="1" @disabled($isOutOfStock)>Mua ngay</button>
            </form>

            <div class="flex items-start gap-4 rounded-xl bg-surface-container-low p-4">
                @include('user-web.partials.icon', ['name' => 'local_shipping', 'class' => 'text-primary'])
                <div class="text-sm">
                    <p class="font-bold">Giao hàng tối ưu theo vùng</p>
                    <p class="text-on-surface-variant">Đơn từ 500.000đ được miễn phí vận chuyển theo chính sách hiện tại.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="mt-20">
        <div class="mb-10 flex gap-12 overflow-x-auto border-b border-outline-variant/15 scrollbar-none">
            @foreach([
                'detail-info' => 'Thông tin chi tiết',
                'detail-usage' => 'Hướng dẫn sử dụng',
            ] as $target => $label)
                <button class="whitespace-nowrap pb-4 font-medium transition-colors {{ $loop->first ? 'border-b-2 border-primary font-bold text-primary' : 'text-on-surface-variant hover:text-primary' }}" type="button" data-tab-group="detail" data-tab-target="{{ $target }}" data-tab-active-class="border-b-2 border-primary font-bold text-primary" data-tab-inactive-class="text-on-surface-variant hover:text-primary">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-16 lg:grid-cols-3">
            <div class="space-y-12 lg:col-span-2">
                <div id="detail-info" data-tab-panel data-tab-group="detail" class="space-y-6">
                    <h3 class="font-headline text-2xl font-bold tracking-tight">Thông tin sản phẩm</h3>
                    <p class="leading-relaxed text-on-surface-variant">{{ $product->description ?: 'Đang cập nhật mô tả sản phẩm.' }}</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-surface-container-lowest p-6">
                            @include('user-web.partials.icon', ['name' => 'verified', 'class' => 'mb-3 text-tertiary'])
                            <h4 class="mb-2 font-bold">Danh mục</h4>
                            <p class="text-sm text-on-surface-variant">{{ $product->category?->name ?? 'Đang cập nhật' }}</p>
                        </div>
                        <div class="rounded-xl bg-surface-container-lowest p-6">
                            @include('user-web.partials.icon', ['name' => 'inventory_2', 'class' => 'mb-3 text-tertiary'])
                            <h4 class="mb-2 font-bold">Nhà cung cấp</h4>
                            <p class="text-sm text-on-surface-variant">{{ $product->supplier?->name ?? $product->region?->name ?? 'Heritage Harvest' }}</p>
                        </div>
                    </div>
                </div>
                <div id="detail-usage" data-tab-panel data-tab-group="detail" class="hidden space-y-6">
                    <h3 class="font-headline text-2xl font-bold tracking-tight">Cách sử dụng sản phẩm</h3>
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach([
                            ['title' => 'Kiểm tra mô tả', 'body' => 'Đọc kỹ phần mô tả sản phẩm để chọn đúng loại đặc sản phù hợp nhu cầu.'],
                            ['title' => 'Thêm vào giỏ', 'body' => 'Chọn số lượng phù hợp rồi thêm sản phẩm vào giỏ để chuẩn bị thanh toán.'],
                            ['title' => 'Checkout', 'body' => 'Đơn hàng sẽ được xác nhận và lưu vào lịch sử đặt hàng của bạn.'],
                        ] as $step)
                            <div class="rounded-2xl bg-surface-container-lowest p-6">
                                <p class="text-xs uppercase tracking-widest text-primary">{{ $step['title'] }}</p>
                                <p class="mt-3 text-sm leading-6 text-on-surface-variant">{{ $step['body'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="space-y-6 rounded-2xl bg-surface-container-high p-8">
                    <h4 class="font-headline text-xl font-bold">Cam kết chất lượng</h4>
                    <ul class="space-y-4">
                        @foreach(['Nguồn gốc rõ ràng', 'Đóng gói đúng chuẩn', 'Hỗ trợ đổi trả theo chính sách'] as $commitment)
                            <li class="flex items-start gap-3">
                                @include('user-web.partials.icon', ['name' => 'task_alt', 'class' => 'text-primary'])
                                <span class="text-sm">{{ $commitment }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>
</section>

@if($relatedProducts->isNotEmpty())
    <section class="mx-auto max-w-screen-2xl px-6 pb-24">
        <div class="mb-10 flex items-center justify-between">
            <h3 class="font-headline text-2xl font-bold tracking-tight">Có thể bạn sẽ thích</h3>
            <a class="font-bold text-primary hover:underline" href="{{ route('user-web.products.index') }}">Xem tất cả</a>
        </div>
        <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
            @foreach($relatedProducts as $relatedProduct)
                <article class="group cursor-pointer">
                    <a href="{{ $ui->productUrl($relatedProduct) }}">
                        <div class="relative mb-4 aspect-[4/5] overflow-hidden rounded-xl bg-surface-container-lowest">
                            @php($relatedImage = $relatedProduct->displayImageUrl() ?? collect($relatedProduct->galleryImageUrls())->first())
                            @if($relatedImage)
                                <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" src="{{ $relatedImage }}" alt="{{ $relatedProduct->name }}">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-primary">
                                    @include('user-web.partials.icon', ['name' => 'inventory_2', 'class' => 'text-5xl'])
                                </div>
                            @endif
                        </div>
                        <h4 class="mb-1 font-headline text-lg font-bold">{{ $relatedProduct->name }}</h4>
                    </a>
                    <p class="font-bold text-primary">{{ $ui->money($relatedProduct->sale_price) }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endif
@endsection
