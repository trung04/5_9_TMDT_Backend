@extends('user-web.layouts.storefront')

@section('title', $mode === 'register' ? 'Đăng ký - Heritage Harvest' : 'Đăng nhập - Heritage Harvest')

@section('content')
<section class="px-6 pb-20 pt-32">
    <div class="mx-auto grid max-w-6xl overflow-hidden rounded-[2.5rem] bg-white shadow-ambient lg:grid-cols-[0.95fr_1.05fr]">
        <div class="hidden bg-primary-glow p-12 text-white lg:block">
            <div class="flex h-full flex-col justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-white/70">Heritage Harvest</p>
                    <h1 class="mt-4 text-4xl font-black tracking-tight">Tài khoản thành viên</h1>
                    <p class="mt-4 leading-7 text-white/75">Quản lý đơn hàng, địa chỉ, điểm thưởng và danh sách sản phẩm yêu thích trong một không gian riêng.</p>
                </div>
                <div class="rounded-3xl bg-white/15 p-5 text-sm text-white/80">Khu vực thành viên đang chạy bằng phiên Laravel.</div>
            </div>
        </div>
        <div class="p-8 sm:p-12">
            <div class="mb-8 flex rounded-full bg-surface-container-low p-1">
                <a class="flex-1 rounded-full px-4 py-3 text-center text-sm font-semibold {{ $mode === 'login' ? 'bg-primary text-on-primary' : 'text-on-surface-variant' }}" href="{{ route('user-web.login', ['redirect' => $redirect]) }}">Đăng nhập</a>
                <a class="flex-1 rounded-full px-4 py-3 text-center text-sm font-semibold {{ $mode === 'register' ? 'bg-primary text-on-primary' : 'text-on-surface-variant' }}" href="{{ route('user-web.register', ['redirect' => $redirect]) }}">Đăng ký</a>
            </div>

            @if($mode === 'register')
                <form class="space-y-4" action="{{ route('user-web.register.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Họ tên</span>
                        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none focus:ring-2 focus:ring-primary/20" name="full_name" value="{{ old('full_name') }}">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Số điện thoại</span>
                        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none focus:ring-2 focus:ring-primary/20" name="phone" value="{{ old('phone') }}">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Email</span>
                        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none focus:ring-2 focus:ring-primary/20" type="email" name="email" value="{{ old('email') }}">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Mật khẩu</span>
                        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none focus:ring-2 focus:ring-primary/20" type="password" name="password">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Nhập lại mật khẩu</span>
                        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none focus:ring-2 focus:ring-primary/20" type="password" name="password_confirmation">
                    </label>
                    <button class="w-full rounded-full bg-primary px-6 py-4 font-semibold text-on-primary" type="submit">Tạo tài khoản</button>
                </form>
            @else
                <form class="space-y-4" action="{{ route('user-web.login.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Email</span>
                        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none focus:ring-2 focus:ring-primary/20" type="email" name="email" value="{{ old('email') }}">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Mật khẩu</span>
                        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none focus:ring-2 focus:ring-primary/20" type="password" name="password">
                    </label>
                    <button class="w-full rounded-full bg-primary px-6 py-4 font-semibold text-on-primary" type="submit">Đăng nhập</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
