@extends('user-web.layouts.storefront')

@section('title', 'Bảo mật - Heritage Harvest')

@section('content')
<section class="px-6 pb-20 pt-28">
    <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[300px_1fr]">
        @include('user-web.partials.account-sidebar')
        <div class="rounded-[2rem] bg-white p-6 shadow-ambient">
            <h1 class="text-3xl font-black text-green-950">Bảo mật</h1>
            <form class="mt-6 max-w-xl space-y-4" action="{{ route('user-web.account.security.password') }}" method="POST">
                @csrf
                @method('PATCH')
                <label class="block space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Mật khẩu hiện tại</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" type="password" name="current_password">
                </label>
                <label class="block space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Mật khẩu moi</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" type="password" name="new_password">
                </label>
                <label class="block space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Nhập lai mật khẩu moi</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" type="password" name="new_password_confirmation">
                </label>
                <button class="rounded-full bg-primary px-8 py-3 font-medium text-on-primary" type="submit">Cập nhật mật khẩu</button>
            </form>
        </div>
    </div>
</section>
@endsection
