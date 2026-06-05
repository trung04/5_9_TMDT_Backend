@extends('user-web.layouts.portal')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', $variant === 'supplier' ? 'Tồn kho nhà cung cấp' : 'Tồn kho kho vận')

@section('content')
    @php
        $inventoryLabels = ['healthy' => 'Ổn định', 'low' => 'Cảnh báo', 'critical' => 'Khẩn cấp'];
        $inventoryTones = ['healthy' => 'success', 'low' => 'warning', 'critical' => 'danger'];
        $requisitionLabels = ['submitted' => 'Đã gửi', 'approved' => 'Đã duyệt', 'received' => 'Đã nhận', 'cancelled' => 'Đã hủy'];
        $exportRoute = $variant === 'supplier' ? 'user-web.supplier.inventory.export' : 'user-web.warehouse.inventory.export';
        $activeItem = $activeInventoryItem ?? null;
        $activeRelated = $activeItem ? $requisitions->where('inventory_sku', $activeItem['sku']) : collect();
    @endphp

    <div class="space-y-8">
        @include('user-web.partials.portal-page-header', [
            'title' => $variant === 'supplier' ? 'Tồn kho nhà cung cấp' : 'Tồn kho kho vận',
            'description' => $variant === 'supplier'
                ? 'Theo dõi tồn kho, trạng thái cảnh báo và lượng hàng đang giữ cho từng SKU.'
                : 'Kiểm tra sức khỏe tồn kho, mức dự trữ và các SKU cần xử lý trong kho.',
            'actions' => '<a class="inline-flex rounded-full bg-surface-container px-5 py-3 text-sm font-semibold text-on-surface-variant transition hover:bg-surface-container-high" href="'.route($exportRoute, request()->query()).'">Xuất CSV</a>',
        ])

        @if($variant === 'warehouse')
            <form class="rounded-[2rem] bg-white p-4 shadow-ambient" action="{{ route('user-web.warehouse.inventory') }}" method="GET">
                <input class="w-full rounded-2xl bg-surface-container-highest px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-primary/15" name="search" value="{{ $query }}" placeholder="Tìm theo SKU, tên sản phẩm hoặc nhà cung cấp...">
            </form>

            <section class="grid gap-6 md:grid-cols-3">
                @include('user-web.partials.portal-stat-card', [
                    'label' => 'Tổng SKU đang theo dõi',
                    'value' => $stats['sku_count'],
                    'icon' => 'inventory',
                    'tone' => 'primary',
                    'delta' => $stats['requisition_count'].' phiếu nhập',
                    'helper' => 'quy mô tồn kho runtime hiện tại',
                ])
                @include('user-web.partials.portal-stat-card', [
                    'label' => 'Cảnh báo tồn kho',
                    'value' => $stats['alert_count'],
                    'icon' => 'warning',
                    'tone' => 'danger',
                    'delta' => 'Cần xử lý',
                    'helper' => 'SKU dưới ngưỡng hoặc mức nguy cấp',
                ])
                @include('user-web.partials.portal-stat-card', [
                    'label' => 'Giá trị tồn kho',
                    'value' => $ui->money($stats['inventory_value']),
                    'icon' => 'payments',
                    'tone' => 'tertiary',
                    'helper' => 'ước tính theo giá nhập hiện có',
                ])
            </section>
        @endif

        @if($variant === 'supplier')
            <div class="grid gap-6 xl:grid-cols-2">
                @forelse($inventory as $item)
                    <article class="space-y-4 rounded-[2rem] bg-white p-6 shadow-ambient">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-on-surface-variant">{{ $item['sku'] }}</p>
                                <h3 class="mt-2 font-headline text-2xl font-semibold">{{ $item['product_name'] ?: $item['product_id'] }}</h3>
                            </div>
                            @include('user-web.partials.portal-badge', [
                                'tone' => $inventoryTones[$item['status']] ?? 'neutral',
                                'label' => $inventoryLabels[$item['status']] ?? $item['status'],
                            ])
                        </div>
                        <div class="grid gap-4 text-sm md:grid-cols-3">
                            <div class="rounded-3xl bg-surface-container-low p-4">
                                <p class="text-on-surface-variant">Tồn thực</p>
                                <p class="mt-2 font-semibold">{{ $item['quantity_on_hand'] }}</p>
                            </div>
                            <div class="rounded-3xl bg-surface-container-low p-4">
                                <p class="text-on-surface-variant">Đã giữ chỗ</p>
                                <p class="mt-2 font-semibold">{{ $item['reserved'] }}</p>
                            </div>
                            <div class="rounded-3xl bg-surface-container-low p-4">
                                <p class="text-on-surface-variant">Ngưỡng tái nhập</p>
                                <p class="mt-2 font-semibold">{{ $item['reorder_level'] }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[2rem] bg-white p-8 text-center text-on-surface-variant shadow-ambient">
                        Chưa có SKU nào trong tồn kho.
                    </div>
                @endforelse
            </div>
        @else
            <section class="rounded-[2rem] bg-white shadow-ambient">
                <div class="border-b border-outline-variant/15 px-6 py-5">
                    <h3 class="font-headline text-xl font-semibold">Danh mục tồn kho</h3>
                </div>
                <div class="overflow-x-auto p-6">
                    <table class="min-w-[980px] w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-widest text-on-surface-variant">
                        <tr>
                            <th class="px-3 py-3">SKU</th>
                            <th class="px-3 py-3">Tồn kho</th>
                            <th class="px-3 py-3">Sản phẩm</th>
                            <th class="px-3 py-3">Giá nhập</th>
                            <th class="px-3 py-3">Trạng thái</th>
                            <th class="px-3 py-3 text-right">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/15">
                        @forelse($inventory as $item)
                            @php($related = $requisitions->where('inventory_sku', $item['sku']))
                            <tr class="align-top hover:bg-primary/5 {{ ($selectedSku ?? null) === $item['sku'] ? 'border-l-4 border-primary bg-primary/5' : '' }}">
                                <td class="px-3 py-4 font-semibold">{{ $item['sku'] }}</td>
                                <td class="px-3 py-4 text-on-surface-variant">{{ $item['quantity_on_hand'] }} / giữ chỗ {{ $item['reserved'] }}</td>
                                <td class="px-3 py-4 text-on-surface-variant">{{ $item['product_name'] ?: $item['product_id'] }}</td>
                                <td class="px-3 py-4 font-semibold">{{ $ui->money($item['purchase_price']) }}</td>
                                <td class="px-3 py-4">
                                    @include('user-web.partials.portal-badge', [
                                        'tone' => $inventoryTones[$item['status']] ?? 'neutral',
                                        'label' => $inventoryLabels[$item['status']] ?? $item['status'],
                                    ])
                                </td>
                                <td class="px-3 py-4 text-right">
                                    <a class="inline-flex rounded-full px-4 py-2 text-sm font-semibold text-primary transition hover:bg-primary/10" href="{{ route('user-web.warehouse.inventory', array_merge(request()->query(), ['selected' => $item['sku']])) }}">
                                        Chọn
                                    </a>
                                    <details class="group text-left">
                                        <summary class="cursor-pointer list-none rounded-full px-4 py-2 text-right text-sm font-semibold text-primary transition hover:bg-primary/10">
                                            Xem chi tiết
                                        </summary>
                                        <div class="mt-4 grid gap-4 rounded-3xl bg-surface-container-low p-4 text-sm sm:grid-cols-2">
                                            <div>
                                                <p class="text-on-surface-variant">Vị trí kệ</p>
                                                <p class="mt-2 font-semibold">{{ $item['aisle'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-on-surface-variant">Ngưỡng nhập lại</p>
                                                <p class="mt-2 font-semibold">{{ $item['reorder_level'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-on-surface-variant">Nhà cung cấp</p>
                                                <p class="mt-2 font-semibold">{{ $item['supplier_name'] ?: $item['supplier_id'] ?: '--' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-on-surface-variant">Phiếu liên quan</p>
                                                <p class="mt-2 font-semibold">{{ $related->count() }}</p>
                                            </div>
                                            <form class="sm:col-span-2 grid gap-3 rounded-2xl bg-white p-4" action="{{ route('user-web.operations.requisitions.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                                <label class="text-sm font-medium">
                                                    Số lượng
                                                    <input class="mt-2 w-full rounded-2xl bg-surface-container-highest px-4 py-3 outline-none focus:ring-2 focus:ring-primary/15" type="number" min="1" name="requested_qty" value="{{ max(1, $item['reorder_level']) }}">
                                                </label>
                                                <label class="text-sm font-medium">
                                                    Ghi chú
                                                    <textarea class="mt-2 min-h-24 w-full rounded-2xl bg-surface-container-highest px-4 py-3 outline-none focus:ring-2 focus:ring-primary/15" name="reason">Cần bổ sung tồn kho cho {{ $item['sku'] }}.</textarea>
                                                </label>
                                                <button class="justify-self-start rounded-full bg-primary px-5 py-3 text-sm font-semibold text-on-primary" type="submit">Tạo phiếu nhập hàng</button>
                                            </form>
                                            @foreach($related as $requisition)
                                                <div class="sm:col-span-2 rounded-2xl bg-white p-4">
                                                    <p class="font-semibold text-on-surface">#{{ $requisition['id'] }}</p>
                                                    <p class="text-on-surface-variant">{{ $requisition['inventory_sku'] }} / SL {{ $requisition['requested_qty'] }} / {{ $requisitionLabels[$requisition['status']] ?? $requisition['status'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-8 text-center text-on-surface-variant" colspan="6">Không có mặt hàng phù hợp.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if($activeItem)
                <section class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
                    <div class="rounded-[2rem] bg-white p-6 shadow-ambient">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-on-surface-variant">Sản phẩm đang chọn</p>
                                <h3 class="mt-2 font-headline text-2xl font-bold text-primary">{{ $activeItem['product_name'] ?: $activeItem['sku'] }}</h3>
                                <p class="mt-1 text-sm text-on-surface-variant">{{ $activeItem['sku'] }}</p>
                            </div>
                            @include('user-web.partials.portal-badge', [
                                'tone' => $inventoryTones[$activeItem['status']] ?? 'neutral',
                                'label' => $inventoryLabels[$activeItem['status']] ?? $activeItem['status'],
                            ])
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                                <p class="text-on-surface-variant">Vị trí kệ</p>
                                <p class="mt-2 font-semibold">{{ $activeItem['aisle'] }}</p>
                            </div>
                            <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                                <p class="text-on-surface-variant">Ngưỡng nhập lại</p>
                                <p class="mt-2 font-semibold">{{ $activeItem['reorder_level'] }}</p>
                            </div>
                            <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                                <p class="text-on-surface-variant">Giá nhập hiện tại</p>
                                <p class="mt-2 font-semibold">{{ $ui->money($activeItem['purchase_price']) }}</p>
                            </div>
                            <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                                <p class="text-on-surface-variant">Nhà cung cấp</p>
                                <p class="mt-2 font-semibold">{{ $activeItem['supplier_name'] ?: $activeItem['supplier_id'] ?: '--' }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h4 class="font-headline text-lg font-semibold">Phiếu liên quan</h4>
                            <div class="mt-3 space-y-3">
                                @forelse($activeRelated as $requisition)
                                    <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                                        <p class="font-semibold text-on-surface">#{{ $requisition['id'] }}</p>
                                        <p class="text-on-surface-variant">{{ $requisition['inventory_sku'] }} / SL {{ $requisition['requested_qty'] }} / {{ $requisitionLabels[$requisition['status']] ?? $requisition['status'] }}</p>
                                    </div>
                                @empty
                                    <p class="rounded-2xl bg-surface-container-low p-4 text-sm text-on-surface-variant">Chưa có phiếu nhập nào cho SKU này.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <form class="rounded-[2rem] bg-white p-6 shadow-ambient" action="{{ route('user-web.operations.requisitions.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $activeItem['product_id'] }}">
                        <div>
                            <p class="text-xs uppercase tracking-widest text-on-surface-variant">Phiếu yêu cầu nhập hàng</p>
                            <h3 class="mt-2 font-headline text-2xl font-bold text-primary">Bộ lập kế hoạch tái nhập</h3>
                        </div>
                        <div class="mt-6 space-y-4">
                            <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                                <p class="font-semibold text-on-surface">{{ $activeItem['sku'] }}</p>
                                <p class="mt-1 text-on-surface-variant">Tồn kho {{ $activeItem['quantity_on_hand'] }} / đã giữ chỗ {{ $activeItem['reserved'] }}</p>
                                <p class="text-on-surface-variant">Giá nhập hiện tại {{ $ui->money($activeItem['purchase_price']) }}</p>
                            </div>
                            <label class="block space-y-2 text-sm">
                                <span class="font-medium text-on-surface">Số lượng yêu cầu</span>
                                <input class="w-full rounded-2xl bg-surface-container-highest px-4 py-3 outline-none focus:ring-2 focus:ring-primary/15" type="number" min="1" name="requested_qty" value="{{ max(20, $activeItem['reorder_level']) }}">
                            </label>
                            <label class="block space-y-2 text-sm">
                                <span class="font-medium text-on-surface">Ghi chú nội bộ</span>
                                <textarea class="min-h-28 w-full rounded-2xl border border-transparent bg-surface-container-highest px-4 py-3 text-sm text-on-surface outline-none transition focus:border-primary/20 focus:ring-2 focus:ring-primary/15" name="reason">Bổ sung tồn kho do nhu cầu tăng.</textarea>
                            </label>
                            <div class="flex justify-end">
                                <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-on-primary" type="submit">Gửi phiếu yêu cầu</button>
                            </div>
                        </div>
                    </form>
                </section>
            @endif
        @endif

        @include('user-web.partials.pagination', ['paginator' => $inventory])
    </div>
@endsection
