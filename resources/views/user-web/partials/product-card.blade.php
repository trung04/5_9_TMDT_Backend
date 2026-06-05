@inject('ui', 'App\Support\UserWeb\UserWebPresenter')
@php
    /** @var \App\Models\Product $product */
    $image = trim((string) $product->image_url);
    $isAvailable = $product->is_active && ! $product->is_deleted && $product->stock_quantity > 0;
@endphp
<article class="group flex flex-col overflow-hidden rounded-xl bg-surface-container-lowest transition-all duration-300 hover:-translate-y-1">
    <a href="{{ $ui->productUrl($product) }}" class="relative aspect-[4/5] overflow-hidden">
        @if($image !== '')
            <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" src="{{ $image }}" alt="{{ $product->name }}">
        @else
            <div class="flex h-full w-full items-center justify-center bg-surface-container-low text-primary">
                @include('user-web.partials.icon', ['name' => 'inventory_2', 'class' => 'text-5xl'])
            </div>
        @endif
        <span class="absolute left-4 top-4 rounded-full bg-primary px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-on-primary shadow-lg">
            {{ $product->region?->name ?? $product->category?->name ?? 'Đặc sản' }}
        </span>
    </a>
    <div class="flex flex-1 flex-col space-y-3 p-6">
        <div class="flex items-start justify-between gap-3">
            <a href="{{ $ui->productUrl($product) }}" class="font-headline text-lg font-semibold leading-tight transition-colors group-hover:text-primary">
                {{ $product->name }}
            </a>
            <div class="flex shrink-0 items-center text-tertiary">
                @include('user-web.partials.icon', ['name' => 'star', 'class' => 'text-[16px]', 'fill' => true])
                <span class="ml-1 text-xs font-bold">4.8</span>
            </div>
        </div>
        <p class="line-clamp-2 text-sm leading-relaxed text-on-surface-variant">{{ $product->short_description ?: $product->description }}</p>
        <div class="mt-auto flex items-center justify-between gap-3 pt-4">
            <span class="text-xl font-bold text-on-surface">{{ $ui->money($product->sale_price) }}</span>
            <form action="{{ route('user-web.cart.items.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-on-primary transition-all active:scale-90 disabled:cursor-not-allowed disabled:bg-surface-container-high disabled:text-on-surface-variant" type="submit" @disabled(! $isAvailable) aria-label="Thêm vào giỏ {{ $product->name }}">
                    @include('user-web.partials.icon', ['name' => 'shopping_basket'])
                </button>
            </form>
        </div>
    </div>
</article>
