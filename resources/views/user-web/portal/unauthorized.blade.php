@extends('user-web.layouts.storefront')

@section('title', 'Không đúng quyền truy cập')

@section('content')
    @php($sessionUser = \Illuminate\Support\Facades\Auth::guard('web')->user())
    <div class="mx-auto flex min-h-[calc(100vh-9rem)] max-w-4xl items-center px-6 py-24">
        <section class="w-full space-y-6 rounded-[2rem] bg-white p-8 text-center shadow-ambient">
            <p class="text-xs uppercase tracking-widest text-error">Không đúng quyền truy cập</p>
            <h1 class="font-headline text-4xl font-bold">Bạn đang ở sai khu vực của hệ thống</h1>
            <p class="mx-auto max-w-2xl text-sm leading-7 text-on-surface-variant">
                @if($sessionUser)
                    Tài khoản hiện tại thuộc vai trò {{ $sessionUser->role }}, nên không thể mở route này.
                @else
                    Bạn cần đăng nhập bằng tài khoản phù hợp để tiếp tục.
                @endif
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a class="rounded-full bg-primary px-6 py-3 font-semibold text-on-primary" href="{{ route('user-web.login') }}">Về trang đăng nhập</a>
                <a class="rounded-full bg-surface-container px-6 py-3 font-semibold text-on-surface-variant" href="{{ route('user-web.home') }}">Về trang chủ</a>
            </div>
            @if($sessionUser)
                <a class="inline-block text-sm font-medium text-primary hover:underline" href="{{ route('user-web.logout') }}">Đăng xuất để đổi tài khoản</a>
            @endif
        </section>
    </div>
@endsection
