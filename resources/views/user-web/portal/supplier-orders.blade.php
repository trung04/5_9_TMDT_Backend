@extends('user-web.layouts.portal')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Đơn hàng nhà cung cấp')

@section('content')
    @php
        $paymentLabels = ['pending' => 'Chờ thanh toán', 'paid' => 'Đã thanh toán', 'cod' => 'Thanh toán khi nhận hàng', 'refunded' => 'Đã hoàn tiền'];
        $deliveryLabels = ['processing' => 'Đang xử lý', 'ready_to_ship' => 'Sẵn sàng giao', 'in_transit' => 'Đang vận chuyển', 'delivered' => 'Đã giao', 'disputed' => 'Có khiếu nại'];
        $shippingLabels = ['standard' => 'Tiêu chuẩn', 'express' => 'Nhanh', 'priority' => 'Ưu tiên'];
        $deliveryTones = ['delivered' => 'success', 'ready_to_ship' => 'warning', 'in_transit' => 'warning', 'disputed' => 'danger', 'processing' => 'primary'];
    @endphp

    <div class="space-y-8">
        @include('user-web.partials.portal-page-header', [
            'title' => 'Đơn hàng nhà cung cấp',
            'description' => 'Theo dõi đơn mua vào, trạng thái thanh toán và tiến độ giao hàng từ nhà cung cấp.',
        ])

        <section class="grid gap-6 lg:grid-cols-3">
            @include('user-web.partials.portal-stat-card', [
                'label' => 'Đơn chờ xử lý',
                'value' => $stats['pending'],
                'icon' => 'inventory_2',
                'tone' => 'primary',
                'helper' => 'đang chờ đóng gói hoặc xác nhận',
            ])
            @include('user-web.partials.portal-stat-card', [
                'label' => 'Đơn đang vận chuyển',
                'value' => $stats['transit'],
                'icon' => 'local_shipping',
                'tone' => 'warning',
                'helper' => 'đã bàn giao đơn vị vận chuyển',
            ])
            @include('user-web.partials.portal-stat-card', [
                'label' => 'Doanh thu',
                'value' => $ui->money($stats['revenue']),
                'icon' => 'payments',
                'tone' => 'secondary',
                'helper' => 'tổng giá trị đơn hàng từ database',
            ])
        </section>

        <section class="rounded-[2rem] bg-white shadow-ambient">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/15 bg-surface-bright px-6 py-5">
                <h4 class="font-headline font-semibold text-on-surface">Danh sách đơn mua vào</h4>
                <div class="flex gap-2">
                    <a class="rounded-full px-4 py-2 text-xs {{ $filter === 'all' ? 'bg-surface-container-low text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-low' }}" href="{{ route('user-web.supplier.orders') }}">Tất cả</a>
                    <a class="rounded-full px-4 py-2 text-xs {{ $filter === 'awaiting' ? 'bg-surface-container-low text-on-surface' : 'text-on-surface-variant hover:bg-surface-container-low' }}" href="{{ route('user-web.supplier.orders', ['filter' => 'awaiting']) }}">Chờ giao kho</a>
                </div>
            </div>
            <div class="overflow-x-auto p-6">
                <table class="min-w-[960px] w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-widest text-on-surface-variant">
                    <tr>
                        <th class="px-3 py-3">Mã đơn</th>
                        <th class="px-3 py-3">Ngày</th>
                        <th class="px-3 py-3">Khách hàng</th>
                        <th class="px-3 py-3">Tổng tiền</th>
                        <th class="px-3 py-3">Thanh toán</th>
                        <th class="px-3 py-3">Trạng thái</th>
                        <th class="px-3 py-3 text-right">Thao tác</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/15">
                    @forelse($orders as $order)
                        <tr class="align-top hover:bg-primary/5 {{ ($selectedOrderId ?? null) === $order['id'] ? 'border-l-4 border-primary bg-primary/5' : '' }}">
                            <td class="px-3 py-4 font-semibold">#{{ $order['id'] }}</td>
                            <td class="px-3 py-4 text-on-surface-variant">{{ $ui->date($order['date']) }}</td>
                            <td class="px-3 py-4">
                                <p class="font-medium">{{ $order['customer_name'] }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $order['supplier_name'] }}</p>
                            </td>
                            <td class="px-3 py-4 font-semibold">{{ $ui->money($order['total']) }}</td>
                            <td class="px-3 py-4">@include('user-web.partials.portal-badge', ['tone' => $order['payment_status'] === 'paid' ? 'success' : 'neutral', 'label' => $paymentLabels[$order['payment_status']] ?? $order['payment_status']])</td>
                            <td class="px-3 py-4">@include('user-web.partials.portal-badge', ['tone' => $deliveryTones[$order['delivery_status']] ?? 'neutral', 'label' => $deliveryLabels[$order['delivery_status']] ?? $order['delivery_status']])</td>
                            <td class="px-3 py-4 text-right">
                                <a class="inline-flex rounded-full px-4 py-2 text-sm font-semibold text-primary hover:bg-primary/10" href="{{ route('user-web.supplier.orders', array_merge(request()->query(), ['selected' => $order['id']])) }}">Chọn</a>
                                <details class="text-left">
                                    <summary class="cursor-pointer list-none rounded-full px-4 py-2 text-sm font-semibold text-primary hover:bg-primary/10">Xem chi tiết</summary>
                                    <div class="mt-4 space-y-4 rounded-3xl bg-surface-container-low p-4">
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <p class="text-on-surface-variant">Người mua</p>
                                                <p class="mt-2 font-semibold">{{ $order['customer_name'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-on-surface-variant">Gói giao hàng</p>
                                                <p class="mt-2 font-semibold">{{ $shippingLabels[$order['shipping_tier']] ?? $order['shipping_tier'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-on-surface-variant">Tổng tiền</p>
                                                <p class="mt-2 font-semibold">{{ $ui->money($order['total']) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-on-surface-variant">Địa chỉ nhận</p>
                                                <p class="mt-2 font-semibold">{{ $order['address'] }}</p>
                                            </div>
                                        </div>
                                        @foreach($order['items'] as $item)
                                            <div class="rounded-2xl bg-white p-4">
                                                <p class="font-semibold text-on-surface">{{ $item['product_name'] ?: $item['product_id'] }}</p>
                                                <p class="text-on-surface-variant">Số lượng {{ $item['quantity'] }} / Đơn giá {{ $ui->money($item['unit_price']) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-3 py-8 text-center text-on-surface-variant" colspan="7">Chưa có đơn hàng.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if($activeOrder)
            <section class="rounded-[2rem] bg-white p-6 shadow-ambient">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-on-surface-variant">Chi tiết đơn đang chọn</p>
                        <h3 class="mt-2 font-headline text-2xl font-bold text-primary">Đơn #{{ $activeOrder['id'] }}</h3>
                        <p class="mt-1 text-sm text-on-surface-variant">{{ $activeOrder['customer_name'] }} / {{ $activeOrder['supplier_name'] }}</p>
                    </div>
                    @include('user-web.partials.portal-badge', ['tone' => $deliveryTones[$activeOrder['delivery_status']] ?? 'neutral', 'label' => $deliveryLabels[$activeOrder['delivery_status']] ?? $activeOrder['delivery_status']])
                </div>

                <div class="mt-6 grid gap-5 xl:grid-cols-3">
                    <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                        <p class="text-on-surface-variant">Người mua</p>
                        <p class="mt-2 font-semibold">{{ $activeOrder['customer_name'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                        <p class="text-on-surface-variant">Gói giao hàng</p>
                        <p class="mt-2 font-semibold">{{ $shippingLabels[$activeOrder['shipping_tier']] ?? $activeOrder['shipping_tier'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                        <p class="text-on-surface-variant">Tổng tiền</p>
                        <p class="mt-2 font-semibold">{{ $ui->money($activeOrder['total']) }}</p>
                    </div>
                    <div class="rounded-2xl bg-surface-container-low p-4 text-sm xl:col-span-3">
                        <p class="text-on-surface-variant">Địa chỉ nhận</p>
                        <p class="mt-2 font-semibold">{{ $activeOrder['address'] }}</p>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @foreach($activeOrder['items'] as $item)
                        <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                            <p class="font-semibold text-on-surface">{{ $item['product_name'] ?: $item['product_id'] }}</p>
                            <p class="text-on-surface-variant">Số lượng {{ $item['quantity'] }} / Đơn giá {{ $ui->money($item['unit_price']) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @include('user-web.partials.pagination', ['paginator' => $orders])
    </div>
@endsection
