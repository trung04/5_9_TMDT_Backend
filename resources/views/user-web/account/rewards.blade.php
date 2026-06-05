@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Điểm thưởng - Heritage Harvest')

@section('content')
@php
    $options = [
        ['title' => 'Voucher miễn phí vận chuyển', 'points' => 120],
        ['title' => 'Quà tặng đặc sản theo mùa', 'points' => 250],
        ['title' => 'Ưu tiên xử lý đơn hàng', 'points' => 400],
    ];
@endphp
<section class="px-6 pb-20 pt-28">
    <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[300px_1fr]">
        @include('user-web.partials.account-sidebar')
        <div class="space-y-6">
            <div class="rounded-[2rem] bg-primary-glow p-8 text-white shadow-ambient">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-white/70">Tổng quan điểm thưởng</p>
                <h1 class="mt-2 text-4xl font-black">{{ $ui->rewardTierLabel($rewardSnapshot['tier'] ?? null) }}</h1>
                <p class="mt-2 text-white/75">{{ $rewardSnapshot['points'] ?? 0 }} điểm · mốc tiếp theo {{ $rewardSnapshot['next_tier_points'] ?? 0 }} điểm</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach($rewardSnapshot['perks'] ?? [] as $perk)
                        <span class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold">{{ $perk }}</span>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach($options as $option)
                    <form class="rounded-[2rem] bg-white p-5 shadow-ambient" action="{{ route('user-web.account.rewards.redeem') }}" method="POST">
                        @csrf
                        <input type="hidden" name="title" value="{{ $option['title'] }}">
                        <input type="hidden" name="points_cost" value="{{ $option['points'] }}">
                        <h2 class="font-black text-green-950">{{ $option['title'] }}</h2>
                        <p class="mt-2 text-sm text-on-surface-variant">{{ $option['points'] }} điểm</p>
                        <button class="mt-5 w-full rounded-full bg-primary px-5 py-3 text-sm font-semibold text-on-primary disabled:opacity-50" type="submit" @disabled(($rewardSnapshot['points'] ?? 0) < $option['points'])>Đổi ngay</button>
                    </form>
                @endforeach
            </div>

            <div class="rounded-[2rem] bg-white p-6 shadow-ambient">
                <h2 class="text-xl font-black text-green-950">Lịch sử đổi điểm</h2>
                <div class="mt-4 space-y-3">
                    @forelse($profile['reward_history'] as $item)
                        <div class="flex justify-between rounded-2xl bg-surface-container-low p-4 text-sm">
                            <div>
                                <p class="font-semibold">{{ $item['title'] }}</p>
                                <p class="text-on-surface-variant">{{ $ui->date($item['created_at'] ?? null) }}</p>
                            </div>
                            <strong>{{ $item['points_used'] ?? 0 }} điểm</strong>
                        </div>
                    @empty
                        <p class="rounded-2xl bg-surface-container-low p-4 text-sm text-on-surface-variant">Chưa có lịch sử đổi điểm.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
