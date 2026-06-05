@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Thanh toán - Heritage Harvest')

@section('content')
@php
    $defaultAddress = collect($profile['addresses'] ?? [])->firstWhere('is_default', true) ?? (($profile['addresses'] ?? [])[0] ?? null);
    $shippingPreview = $subtotal >= 500000 ? 0 : null;
@endphp

<section class="px-6 pb-20 pt-28">
    <div class="mx-auto max-w-screen-2xl">
        <div class="mb-8">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-primary">Thanh toán</p>
            <h1 class="mt-2 font-headline text-4xl font-black text-green-950">Giỏ hàng & giao hàng</h1>
            <p class="mt-3 max-w-2xl text-on-surface-variant">
                Kiểm tra giỏ hàng, chọn địa chỉ nhận và phương thức thanh toán trước khi hoàn tất đơn.
            </p>
        </div>

        @if($isGuestCheckout)
            <div class="mb-6 rounded-[2rem] border border-primary/15 bg-primary/5 p-5 text-sm text-primary">
                Bạn có thể chuẩn bị giỏ hàng khi chưa đăng nhập. Khi đặt hàng, hệ thống sẽ chuyển sang đăng nhập và giữ lại giỏ hàng hiện tại.
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px]">
            <div class="space-y-6">
                <section class="rounded-[2rem] bg-white p-6 shadow-ambient">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="font-headline text-2xl font-semibold">Sản phẩm trong giỏ</h2>
                            <p class="mt-1 text-sm text-on-surface-variant">{{ $itemCount }} sản phẩm</p>
                        </div>
                        <a class="rounded-full bg-surface-container px-4 py-2 text-sm font-semibold text-on-surface-variant hover:text-primary" href="{{ route('user-web.products.index') }}">
                            Tiếp tục mua sắm
                        </a>
                    </div>

                    <div class="space-y-4">
                        @forelse($items as $item)
                            @php
                                $product = $item['product'];
                                $lineKey = $item['cart_item_id'] ?? $item['product_id'];
                                $stock = max(1, (int) ($product->stock_quantity ?? 999));
                            @endphp
                            <article class="flex flex-col gap-5 rounded-xl bg-surface-container-lowest p-5 sm:flex-row sm:items-center" data-cart-line>
                                <a class="block overflow-hidden rounded-2xl bg-surface-container-low sm:h-24 sm:w-24" href="{{ $ui->productUrl($product) }}">
                                    @if($product->image_url)
                                        <img class="h-40 w-full object-cover sm:h-24 sm:w-24" src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="flex h-40 w-full items-center justify-center text-primary sm:h-24 sm:w-24">
                                            @include('user-web.partials.icon', ['name' => 'inventory_2', 'class' => 'text-4xl'])
                                        </div>
                                    @endif
                                </a>

                                <div class="min-w-0 flex-1">
                                    <a class="font-headline text-lg font-semibold text-on-surface hover:text-primary" href="{{ $ui->productUrl($product) }}">
                                        {{ $product->name }}
                                    </a>
                                    <p class="mt-1 text-sm text-on-surface-variant">{{ $product->category?->name ?? 'Danh mục' }} · {{ $product->supplier?->name ?? 'Heritage Harvest' }}</p>
                                    <p class="mt-2 text-sm font-semibold text-primary">{{ $ui->money($item['unit_price']) }}</p>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                                    <div class="flex items-center rounded-full bg-surface-container-low px-2 py-1">
                                        <form action="{{ route('user-web.cart.items.update', $lineKey) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ max(0, (int) $item['quantity'] - 1) }}">
                                            <button class="flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-white" type="submit" aria-label="Giảm số lượng {{ $product->name }}">
                                                @include('user-web.partials.icon', ['name' => 'remove', 'class' => 'text-sm'])
                                            </button>
                                        </form>
                                        <form action="{{ route('user-web.cart.items.update', $lineKey) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input class="w-14 border-none bg-transparent text-center text-sm font-semibold outline-none" name="quantity" type="number" min="0" max="{{ $stock }}" value="{{ $item['quantity'] }}" data-line-price="{{ $item['unit_price'] }}" data-submit-on-change aria-label="Số lượng {{ $product->name }}">
                                        </form>
                                        <form action="{{ route('user-web.cart.items.update', $lineKey) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ min($stock, (int) $item['quantity'] + 1) }}">
                                            <button class="flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-white" type="submit" aria-label="Tăng số lượng {{ $product->name }}">
                                                @include('user-web.partials.icon', ['name' => 'add', 'class' => 'text-sm'])
                                            </button>
                                        </form>
                                    </div>

                                    <p class="min-w-24 text-right font-bold text-on-surface" data-line-total>{{ $ui->money($item['line_total']) }}</p>

                                    <form action="{{ route('user-web.cart.items.destroy', $lineKey) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-full p-2 text-zinc-400 transition-colors hover:bg-error/10 hover:text-error" type="submit" aria-label="Xóa {{ $product->name }}">
                                            @include('user-web.partials.icon', ['name' => 'delete'])
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl bg-surface-container-lowest p-10 text-center">
                                <p class="font-medium text-on-surface-variant">Chưa có sản phẩm nào trong giỏ.</p>
                                <a class="mt-5 inline-flex rounded-full bg-primary px-6 py-3 font-semibold text-on-primary" href="{{ route('user-web.products.index') }}">
                                    Khám phá cửa hàng
                                </a>
                            </div>
                        @endforelse
                    </div>
                </section>

                <form id="checkout-form" class="space-y-6 rounded-[2rem] bg-white p-6 shadow-ambient" action="{{ route('user-web.checkout.place') }}" method="POST">
                    @csrf
                    <section>
                        <h2 class="font-headline text-2xl font-semibold">Thông tin nhận hàng</h2>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <label class="space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Người nhận</span>
                                <input class="w-full rounded-xl border-b-2 border-transparent bg-surface-container-highest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-0" name="recipient_name" value="{{ old('recipient_name', $defaultAddress['recipient'] ?? $profile['name'] ?? '') }}">
                            </label>
                            <label class="space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Điện thoại</span>
                                <input class="w-full rounded-xl border-b-2 border-transparent bg-surface-container-highest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-0" name="recipient_phone" value="{{ old('recipient_phone', $defaultAddress['phone'] ?? $profile['phone'] ?? '') }}">
                            </label>
                            <label class="space-y-2 sm:col-span-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Địa chỉ cụ thể</span>
                                <input class="w-full rounded-xl border-b-2 border-transparent bg-surface-container-highest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-0" name="shipping_line1" value="{{ old('shipping_line1', $defaultAddress['line1'] ?? $profile['address'] ?? '') }}">
                            </label>
                            <input type="hidden" name="shipping_address" value="{{ old('shipping_address', $defaultAddress ? collect([$defaultAddress['line1'], $defaultAddress['ghn_ward_name'], $defaultAddress['ghn_district_name'], $defaultAddress['ghn_province_name']])->filter()->implode(', ') : ($profile['address'] ?? '')) }}">
                            <label class="space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Tỉnh/Thành</span>
                                <input type="hidden" name="shipping_province_name" value="{{ old('shipping_province_name', $defaultAddress['ghn_province_name'] ?? $profile['city'] ?? '') }}">
                                <select class="w-full rounded-xl border-b-2 border-transparent bg-surface-container-highest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-0" name="shipping_province_id" data-location-level="province" data-location-next="[name='shipping_district_id']" data-location-name-target="shipping_province_name">
                                    <option value="">Chọn tỉnh/thành</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province['ProvinceID'] }}" @selected((int) old('shipping_province_id', $defaultAddress['ghn_province_id'] ?? 0) === $province['ProvinceID'])>{{ $province['ProvinceName'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Quận/Huyện</span>
                                <input type="hidden" name="shipping_district_name" value="{{ old('shipping_district_name', $defaultAddress['ghn_district_name'] ?? '') }}">
                                <select class="w-full rounded-xl border-b-2 border-transparent bg-surface-container-highest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-0" name="shipping_district_id" data-location-level="district" data-location-next="[name='shipping_ward_code']" data-location-name-target="shipping_district_name">
                                    <option value="{{ old('shipping_district_id', $defaultAddress['ghn_district_id'] ?? '') }}">{{ old('shipping_district_name', $defaultAddress['ghn_district_name'] ?? 'Chọn quận/huyện') }}</option>
                                </select>
                            </label>
                            <label class="space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Phường/Xã</span>
                                <input type="hidden" name="shipping_ward_name" value="{{ old('shipping_ward_name', $defaultAddress['ghn_ward_name'] ?? '') }}">
                                <select class="w-full rounded-xl border-b-2 border-transparent bg-surface-container-highest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-0" name="shipping_ward_code" data-location-name-target="shipping_ward_name">
                                    <option value="{{ old('shipping_ward_code', $defaultAddress['ghn_ward_code'] ?? '') }}">{{ old('shipping_ward_name', $defaultAddress['ghn_ward_name'] ?? 'Chọn phường/xã') }}</option>
                                </select>
                            </label>
                            <label class="space-y-2">
                                <span class="text-sm font-semibold text-on-surface-variant">Ghi chú</span>
                                <textarea class="min-h-24 w-full rounded-xl border-b-2 border-transparent bg-surface-container-highest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-0" name="note">{{ old('note') }}</textarea>
                            </label>
                        </div>
                    </section>

                    <section>
                        <h3 class="font-headline text-xl font-semibold">Phương thức thanh toán</h3>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach([
                                \App\Models\Order::PAYMENT_METHOD_COD => ['title' => 'Thanh toán khi nhận hàng', 'description' => 'Thanh toán trực tiếp khi đơn được giao thành công.'],
                                \App\Models\Order::PAYMENT_METHOD_BANK_TRANSFER => ['title' => 'Chuyển khoản ngân hàng', 'description' => 'Nhận hướng dẫn chuyển khoản sau khi tạo đơn.'],
                            ] as $method => $copy)
                                <label class="flex cursor-pointer gap-3 rounded-3xl border border-outline-variant/20 bg-surface-container-lowest p-4 transition hover:border-primary/30">
                                    <input class="mt-1 text-primary" type="radio" name="payment_method" value="{{ $method }}" @checked(old('payment_method', \App\Models\Order::PAYMENT_METHOD_COD) === $method)>
                                    <span>
                                        <span class="block font-semibold">{{ $copy['title'] }}</span>
                                        <span class="mt-1 block text-sm text-on-surface-variant">{{ $copy['description'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                </form>
            </div>

            <aside class="h-max rounded-[2rem] bg-white p-6 shadow-ambient">
                <h2 class="font-headline text-2xl font-semibold">Tóm tắt đơn hàng</h2>
                <div class="mt-6 space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Tạm tính</span>
                        <strong>{{ $ui->money($subtotal) }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Vận chuyển</span>
                        <strong>{{ $shippingPreview === 0 ? 'Miễn phí' : 'Tự tính khi đặt hàng' }}</strong>
                    </div>
                    <div class="border-t border-outline-variant/20 pt-4">
                        <div class="flex justify-between text-base">
                            <span>Tổng hiện tại</span>
                            <strong class="text-primary">{{ $ui->money($subtotal + (float) ($shippingPreview ?? 0)) }}</strong>
                        </div>
                    </div>
                </div>
                <button class="mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-4 font-semibold text-on-primary transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50" form="checkout-form" type="submit" @disabled(count($items) === 0)>
                    @include('user-web.partials.icon', ['name' => $isGuestCheckout ? 'login' : 'check_circle'])
                    {{ $isGuestCheckout ? 'Đăng nhập để đặt hàng' : 'Đặt hàng' }}
                </button>
            </aside>
        </div>
    </div>
</section>
@endsection
