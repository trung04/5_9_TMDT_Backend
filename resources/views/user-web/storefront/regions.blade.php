@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Vùng miền - Heritage Harvest')

@section('content')
<section class="px-6 pb-16 pt-28">
    <div class="mx-auto max-w-7xl space-y-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-primary">Bản đồ nguồn gốc</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight text-green-950">Hành trình nguồn gốc</h1>
            <p class="mt-3 max-w-2xl text-on-surface-variant">Theo dấu từng vùng nguyên liệu và các sản phẩm đang có trên cửa hàng.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($regions as $region)
                <section class="rounded-[2rem] bg-white p-6 shadow-ambient">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-black text-green-950">{{ $region->name }}</h2>
                            <p class="mt-1 text-sm text-on-surface-variant">{{ $region->products_count }} sản phẩm</p>
                        </div>
                        <div class="rounded-full bg-primary/10 p-3 text-primary">
                            @include('user-web.partials.icon', ['name' => 'location_on'])
                        </div>
                    </div>
                    <p class="line-clamp-3 text-sm leading-6 text-on-surface-variant">{{ $region->description ?: 'Vùng nguyên liệu nổi bật trong mạng lưới Heritage Harvest.' }}</p>
                    <div class="mt-5 space-y-2">
                        @foreach(($productsByRegion[$region->id] ?? collect())->take(3) as $product)
                            <a class="block rounded-2xl bg-surface-container-low px-4 py-3 text-sm font-semibold hover:text-primary" href="{{ $ui->productUrl($product) }}">
                                {{ $product->name }}
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</section>
@endsection
