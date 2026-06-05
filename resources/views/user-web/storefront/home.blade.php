@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Heritage Harvest')

@section('content')
<section class="relative overflow-hidden px-6 pb-20 pt-32">
    <div class="mx-auto grid max-w-screen-2xl items-center gap-12 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-8">
            <div class="inline-flex items-center gap-2 rounded-full bg-primary-fixed px-4 py-2 text-sm font-semibold text-on-primary-fixed">
                @include('user-web.partials.icon', ['name' => 'eco', 'class' => 'text-lg'])
                Đặc sản Việt được tuyển chọn
            </div>
            <div class="space-y-5">
                <h1 class="max-w-4xl text-5xl font-black tracking-tight text-green-950 sm:text-6xl lg:text-7xl">
                    Heritage Harvest
                </h1>
                <p class="max-w-2xl text-lg leading-8 text-on-surface-variant">
                    Kết nối câu chuyện vùng miền, nhà sản xuất địa phương và những món quà nông sản thủ công được chọn lọc kỹ lưỡng.
                </p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <a class="inline-flex items-center justify-center rounded-full bg-primary px-7 py-4 font-semibold text-on-primary shadow-lg shadow-primary/20 transition active:scale-95" href="{{ route('user-web.products.index') }}">
                    Khám phá cửa hàng
                </a>
                <a class="inline-flex items-center justify-center rounded-full border border-outline-variant px-7 py-4 font-semibold text-primary transition hover:bg-primary/5" href="{{ route('user-web.story') }}">
                    Đọc câu chuyện
                </a>
            </div>
        </div>
        <div class="relative">
            <div class="overflow-hidden rounded-[2.5rem] bg-surface-container-low shadow-ambient">
                @php($heroProduct = $featuredProducts->first())
                @if($heroProduct?->image_url)
                    <img class="h-[540px] w-full object-cover" src="{{ $heroProduct->image_url }}" alt="{{ $heroProduct->name }}">
                @else
                    <div class="flex h-[540px] w-full items-center justify-center bg-primary-fixed-dim text-on-primary-fixed">
                        @include('user-web.partials.icon', ['name' => 'agriculture', 'class' => 'text-8xl'])
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="px-6 py-16">
    <div class="mx-auto max-w-7xl space-y-8">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-primary">Sản phẩm nổi bật</p>
                <h2 class="mt-2 text-3xl font-black text-green-950">Lựa chọn theo mùa</h2>
            </div>
            <a class="text-sm font-semibold text-primary hover:underline" href="{{ route('user-web.products.index') }}">Xem tất cả</a>
        </div>
        @if($featuredProducts->isEmpty())
            <div class="rounded-[2rem] bg-surface-container-low p-8 text-sm text-on-surface-variant">Chưa có sản phẩm để hiển thị.</div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($featuredProducts as $product)
                    @include('user-web.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="px-6 py-16">
    <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-2">
        <div class="rounded-[2rem] bg-surface-container-low p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-2xl font-black text-green-950">Danh mục</h2>
                @include('user-web.partials.icon', ['name' => 'category', 'class' => 'text-primary'])
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($categories as $category)
                    <a class="rounded-3xl bg-white p-4 transition hover:-translate-y-0.5 hover:text-primary" href="{{ route('user-web.products.index', ['categories' => $category->id]) }}">
                        <p class="font-bold">{{ $category->name }}</p>
                        <p class="mt-1 line-clamp-2 text-sm text-on-surface-variant">{{ $category->description ?: 'Bộ sưu tập đặc sản được chọn lọc.' }}</p>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="rounded-[2rem] bg-surface-container-low p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-2xl font-black text-green-950">Vùng miền</h2>
                @include('user-web.partials.icon', ['name' => 'map', 'class' => 'text-primary'])
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($regions as $region)
                    <a class="rounded-3xl bg-white p-4 transition hover:-translate-y-0.5 hover:text-primary" href="{{ route('user-web.products.index', ['regions' => $region->id]) }}">
                        <p class="font-bold">{{ $region->name }}</p>
                        <p class="mt-1 text-sm text-on-surface-variant">{{ $region->products_count }} sản phẩm</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="px-6 py-16">
    <div class="mx-auto max-w-7xl rounded-[2rem] bg-primary-glow p-8 text-white sm:p-12">
        <div class="grid gap-8 lg:grid-cols-[1fr_0.8fr] lg:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-white/70">Bản tin</p>
                <h2 class="mt-2 text-3xl font-black">Nhận câu chuyện mùa vụ mới</h2>
                <p class="mt-3 max-w-2xl text-white/75">Cập nhật sản phẩm mới, ưu đãi thành viên và hành trình nguồn gốc từ Heritage Harvest.</p>
            </div>
            <form class="flex flex-col gap-3 sm:flex-row" action="{{ route('user-web.newsletter.store') }}" method="POST">
                @csrf
                <input type="hidden" name="source" value="home">
                <input class="min-h-12 flex-1 rounded-full border-none px-5 text-on-surface outline-none" type="email" name="email" placeholder="Email của bạn">
                <button class="rounded-full bg-white px-6 py-3 font-bold text-primary" type="submit">Đăng ký</button>
            </form>
        </div>
    </div>
</section>
@endsection
