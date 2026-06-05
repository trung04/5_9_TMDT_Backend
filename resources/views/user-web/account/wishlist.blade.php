@extends('user-web.layouts.storefront')

@section('title', 'Yeu thich - Heritage Harvest')

@section('content')
<section class="px-6 pb-20 pt-28">
    <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[300px_1fr]">
        @include('user-web.partials.account-sidebar')
        <div class="space-y-6">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-primary">Wishlist</p>
                <h1 class="mt-2 text-3xl font-black text-green-950">Danh sách yêu thích</h1>
            </div>
            @if($wishlistProducts->isEmpty())
                <div class="rounded-[2rem] bg-white p-6 text-sm text-on-surface-variant shadow-ambient">Chưa có sản phẩm yeu thich.</div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($wishlistProducts as $product)
                        <div class="relative">
                            @include('user-web.partials.product-card', ['product' => $product])
                            <form class="absolute right-4 top-4" action="{{ route('user-web.account.wishlist.destroy', $product->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-full bg-white/90 p-2 text-error shadow" type="submit" aria-label="Xóa yeu thich">
                                    @include('user-web.partials.icon', ['name' => 'delete'])
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
