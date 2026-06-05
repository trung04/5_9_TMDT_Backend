@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Thông báo - Heritage Harvest')

@section('content')
<section class="px-6 pb-20 pt-28">
    <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[300px_1fr]">
        @include('user-web.partials.account-sidebar')
        <div class="space-y-6">
            <div class="rounded-[2rem] bg-white p-6 shadow-ambient">
                <h1 class="text-3xl font-black text-green-950">Thông báo</h1>
                <form class="mt-5 grid gap-3 sm:grid-cols-2" action="{{ route('user-web.account.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $profile['name'] }}">
                    <input type="hidden" name="phone" value="{{ $profile['phone'] }}">
                    @foreach(['newsletter' => 'Bản tin', 'sms_alerts' => 'SMS', 'order_email' => 'Email đơn hàng', 'security_alerts' => 'Bảo mật'] as $field => $label)
                        <label class="rounded-2xl bg-surface-container-low p-4 text-sm font-semibold">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked($profile[$field])>
                            <span class="ml-2">{{ $label }}</span>
                        </label>
                    @endforeach
                    <button class="w-max rounded-full bg-primary px-6 py-3 text-sm font-semibold text-on-primary" type="submit">Lưu tùy chọn</button>
                </form>
            </div>

            <div class="space-y-3">
                @forelse($notifications as $notification)
                    <article class="rounded-[2rem] bg-white p-5 shadow-ambient">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                            <div>
                                <p class="font-bold">{{ $notification['title'] }}</p>
                                <p class="mt-1 text-sm text-on-surface-variant">{{ $notification['message'] }}</p>
                                <p class="mt-2 text-xs text-on-surface-variant">{{ $ui->date($notification['created_at']) }} · {{ $ui->notificationStatusLabel($notification['status'] ?? null) }}</p>
                            </div>
                            @if(empty($notification['read_at']))
                                <form action="{{ route('user-web.account.notifications.read', $notification['id']) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-full bg-primary/10 px-4 py-2 text-sm font-semibold text-primary" type="submit">Đã đọc</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-[2rem] bg-white p-6 text-sm text-on-surface-variant shadow-ambient">Chưa có thông báo.</div>
                @endforelse
                @include('user-web.partials.pagination', ['paginator' => $notifications])
            </div>
        </div>
    </div>
</section>
@endsection
