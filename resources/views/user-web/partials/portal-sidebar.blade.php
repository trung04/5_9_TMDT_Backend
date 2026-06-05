@php
    $variant = $variant ?? 'supplier';
    $items = $variant === 'supplier'
        ? [
            ['label' => 'Tồn kho', 'url' => route('user-web.supplier.inventory'), 'icon' => 'inventory_2', 'match' => 'supplier/inventory*'],
            ['label' => 'Phiếu yêu cầu', 'url' => route('user-web.supplier.requisitions'), 'icon' => 'assignment_turned_in', 'match' => 'supplier/requisitions*'],
            ['label' => 'Xử lý đơn', 'url' => route('user-web.supplier.processing'), 'icon' => 'package_2', 'match' => 'supplier/processing*'],
            ['label' => 'Đơn nhà cung cấp', 'url' => route('user-web.supplier.orders'), 'icon' => 'local_shipping', 'match' => 'supplier/orders*'],
        ]
        : [
            ['label' => 'Tồn kho', 'url' => route('user-web.warehouse.inventory'), 'icon' => 'inventory_2', 'match' => 'warehouse/inventory*'],
            ['label' => 'Phiếu tái nhập', 'url' => route('user-web.warehouse.requisitions'), 'icon' => 'assignment_turned_in', 'match' => 'warehouse/requisitions*'],
            ['label' => 'Fulfillment', 'url' => route('user-web.warehouse.fulfillment'), 'icon' => 'package_2', 'match' => 'warehouse/fulfillment*'],
            ['label' => 'Đơn theo nhà cung cấp', 'url' => route('user-web.warehouse.supplier-orders'), 'icon' => 'local_shipping', 'match' => 'warehouse/supplier-orders*'],
        ];
    $helpUrl = $variant === 'supplier' ? route('user-web.supplier.help') : route('user-web.warehouse.help');
    $helpMatch = $variant === 'supplier' ? 'supplier/help*' : 'warehouse/help*';
@endphp
<aside class="flex h-full flex-col bg-[#f3f3f3] p-4 shadow-[20px_0px_40px_rgba(26,28,28,0.03)]">
    <div class="mb-8 px-4 py-2">
        <h2 class="font-headline text-lg font-bold tracking-tight text-primary">Heritage Harvest</h2>
        <p class="mt-1 text-[11px] uppercase tracking-widest text-on-surface-variant/70">
            {{ $variant === 'supplier' ? 'Cổng nhà cung cấp' : 'Cổng kho vận' }}
        </p>
    </div>

    <nav class="space-y-1">
        @foreach($items as $item)
            <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ request()->is($item['match']) ? 'bg-white text-primary shadow-sm' : 'text-on-surface-variant hover:bg-white/40 hover:text-primary' }}" href="{{ $item['url'] }}">
                @include('user-web.partials.icon', ['name' => $item['icon'], 'class' => 'text-xl'])
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="mt-auto space-y-1 border-t border-outline-variant/20 pt-4">
        <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ request()->is($helpMatch) ? 'bg-white text-primary shadow-sm' : 'text-on-surface-variant hover:bg-white/40 hover:text-primary' }}" href="{{ $helpUrl }}">
            @include('user-web.partials.icon', ['name' => 'help', 'class' => 'text-xl'])
            <span>Trung tam hỗ trợ</span>
        </a>
        <a class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-on-surface-variant transition hover:bg-white/40 hover:text-primary" href="{{ route('user-web.logout') }}">
            @include('user-web.partials.icon', ['name' => 'logout', 'class' => 'text-xl'])
            <span>Đăng xuất</span>
        </a>
    </div>
</aside>
