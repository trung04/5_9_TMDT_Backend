@extends('user-web.layouts.storefront')

@section('title', 'Địa chỉ - Heritage Harvest')

@section('content')
<section class="px-6 pb-20 pt-28">
    <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[300px_1fr]">
        @include('user-web.partials.account-sidebar')
        <div class="space-y-6">
            <div class="rounded-[2rem] bg-white p-6 shadow-ambient">
                <h1 class="text-3xl font-black text-green-950">Địa chỉ giao hàng</h1>
                @include('user-web.account.partials.address-form', ['action' => route('user-web.account.addresses.store'), 'method' => 'POST', 'address' => null])
            </div>
            <div class="grid gap-4">
                @foreach($profile['addresses'] as $address)
                    <article class="rounded-[2rem] bg-white p-5 shadow-ambient">
                        <div class="flex flex-col justify-between gap-4 md:flex-row">
                            <div>
                                <p class="font-bold">{{ $address['label'] }} @if($address['is_default']) <span class="rounded-full bg-primary/10 px-2 py-1 text-xs text-primary">Mac dinh</span> @endif</p>
                                <p class="mt-2 text-sm text-on-surface-variant">{{ $address['recipient'] }} · {{ $address['phone'] }}</p>
                                <p class="mt-1 text-sm text-on-surface-variant">{{ collect([$address['line1'], $address['ghn_ward_name'], $address['ghn_district_name'], $address['ghn_province_name'] ?: $address['city']])->filter()->implode(', ') }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if(! $address['is_default'])
                                    <form action="{{ route('user-web.account.addresses.default', $address['id']) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-full bg-primary/10 px-4 py-2 text-sm font-semibold text-primary" type="submit">Mac dinh</button>
                                    </form>
                                @endif
                                <form action="{{ route('user-web.account.addresses.destroy', $address['id']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-full bg-error/10 px-4 py-2 text-sm font-semibold text-error" type="submit" data-confirm="Xóa địa chỉ nay?">Xóa</button>
                                </form>
                            </div>
                        </div>
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm font-semibold text-primary">Sua địa chỉ</summary>
                            @include('user-web.account.partials.address-form', ['action' => route('user-web.account.addresses.update', $address['id']), 'method' => 'PUT', 'address' => $address])
                        </details>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
