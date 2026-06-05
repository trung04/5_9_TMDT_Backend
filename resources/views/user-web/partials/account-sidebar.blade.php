@inject('ui', 'App\Support\UserWeb\UserWebPresenter')
<aside class="space-y-6">
    <div class="rounded-3xl bg-primary-glow p-6 text-white shadow-ambient">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl font-bold">
                {{ mb_substr($profile['name'] ?? 'H', 0, 1) }}
            </div>
            <div>
                <p class="font-bold">{{ $profile['name'] ?? 'Khách hàng' }}</p>
                <p class="text-sm text-white/75">{{ $profile['email'] ?? '' }}</p>
            </div>
        </div>
        <div class="mt-5 rounded-2xl bg-white/15 p-4 text-sm">
            <p class="text-white/75">Hạng thành viên</p>
            <p class="mt-1 font-bold">{{ $ui->rewardTierLabel($rewardSnapshot['tier'] ?? null) }} · {{ $rewardSnapshot['points'] ?? 0 }} điểm</p>
        </div>
    </div>

    <div class="rounded-3xl bg-surface-container-low p-4">
        <nav class="flex flex-col gap-2">
            @foreach([
                ['label' => 'Thông tin cá nhân', 'url' => route('user-web.account.profile'), 'match' => 'account/profile'],
                ['label' => 'Danh sách yêu thích', 'url' => route('user-web.account.wishlist'), 'match' => 'account/wishlist'],
                ['label' => 'Lịch sử đơn hàng', 'url' => route('user-web.account.orders'), 'match' => 'account/orders*'],
                ['label' => 'Địa chỉ giao hàng', 'url' => route('user-web.account.addresses'), 'match' => 'account/addresses'],
                ['label' => 'Thông báo', 'url' => route('user-web.account.notifications'), 'match' => 'account/notifications'],
                ['label' => 'Điểm thưởng', 'url' => route('user-web.account.rewards'), 'match' => 'account/rewards'],
                ['label' => 'Bảo mật', 'url' => route('user-web.account.security'), 'match' => 'account/security'],
                ['label' => 'Khiếu nại', 'url' => route('user-web.account.disputes'), 'match' => 'account/disputes'],
            ] as $item)
                <a class="block rounded-full px-4 py-2.5 text-sm font-medium transition {{ request()->is($item['match']) ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}" href="{{ $item['url'] }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a class="block rounded-full px-4 py-2.5 text-sm font-medium text-error transition hover:bg-surface-container hover:text-error" href="{{ route('user-web.logout') }}">
                Đăng xuất
            </a>
        </nav>
    </div>
</aside>
