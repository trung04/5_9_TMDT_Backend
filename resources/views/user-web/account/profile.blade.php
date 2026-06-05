@extends('user-web.layouts.storefront')

@section('title', 'Tài khoản - Heritage Harvest')

@section('content')
<section class="px-6 pb-20 pt-28">
    <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[300px_1fr]">
        @include('user-web.partials.account-sidebar')
        <div class="rounded-[2rem] bg-white p-6 shadow-ambient">
            <h1 class="text-3xl font-black text-green-950">Thông tin cá nhân</h1>
            <form class="mt-6 grid gap-4 sm:grid-cols-2" action="{{ route('user-web.account.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Họ tên</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="name" value="{{ old('name', $profile['name']) }}">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Email</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" value="{{ $profile['email'] }}" disabled>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Số điện thoại</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="phone" value="{{ old('phone', $profile['phone']) }}">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Vùng yêu thích</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="favorite_region" value="{{ old('favorite_region', $profile['favorite_region']) }}">
                </label>
                <label class="space-y-2 sm:col-span-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Địa chỉ</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="address" value="{{ old('address', $profile['address']) }}">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">Thành phố</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="city" value="{{ old('city', $profile['city']) }}">
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-on-surface-variant">URL ảnh đại diện</span>
                    <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="avatar" value="{{ old('avatar', $profile['avatar']) }}">
                </label>
                <div class="sm:col-span-2 grid gap-3 sm:grid-cols-2">
                    @foreach(['newsletter' => 'Nhận bản tin', 'sms_alerts' => 'Cảnh báo SMS', 'order_email' => 'Email đơn hàng', 'security_alerts' => 'Cảnh báo bảo mật'] as $field => $label)
                        <label class="rounded-2xl bg-surface-container-low p-4 text-sm font-semibold">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $profile[$field]))>
                            <span class="ml-2">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="sm:col-span-2">
                    <button class="rounded-full bg-primary px-8 py-3 font-medium text-on-primary shadow-lg transition-all active:scale-95" type="submit">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
