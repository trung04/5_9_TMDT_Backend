@extends('user-web.layouts.portal')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Đơn nhà cung cấp tại kho')

@section('content')
    @php
        $paymentLabels = ['pending' => 'Chờ thanh toán', 'paid' => 'Đã thanh toán', 'cod' => 'Thanh toán khi nhận hàng', 'refunded' => 'Đã hoàn tiền'];
        $deliveryLabels = ['processing' => 'Đang xử lý', 'ready_to_ship' => 'Sẵn sàng giao', 'in_transit' => 'Đang vận chuyển', 'delivered' => 'Đã giao', 'disputed' => 'Có khiếu nại'];
        $deliveryTones = ['delivered' => 'success', 'ready_to_ship' => 'warning', 'in_transit' => 'warning', 'disputed' => 'danger', 'processing' => 'primary'];
    @endphp

    <div class="space-y-8">
        @include('user-web.partials.portal-page-header', [
            'title' => 'Đơn nhà cung cấp tại kho',
            'description' => 'Theo dõi đơn từ nhà cung cấp, lịch nhận hàng và trạng thái xử lý tại kho.',
        ])

        <section class="rounded-[2rem] bg-white shadow-ambient">
            <div class="border-b border-outline-variant/15 px-6 py-5">
                <h3 class="font-headline text-xl font-semibold">Đơn nhà cung cấp tại kho</h3>
            </div>
            <div class="overflow-x-auto p-6">
                <table class="min-w-[960px] w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-widest text-on-surface-variant">
                    <tr>
                        <th class="px-3 py-3">Đơn hàng</th>
                        <th class="px-3 py-3">Nhà cung cấp</th>
                        <th class="px-3 py-3">Ngày</th>
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
                            <td class="px-3 py-4">
                                <p class="font-medium">{{ $order['supplier_name'] }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $order['customer_name'] }}</p>
                            </td>
                            <td class="px-3 py-4 text-on-surface-variant">{{ $ui->date($order['date']) }}</td>
                            <td class="px-3 py-4 font-semibold">{{ $ui->money($order['total']) }}</td>
                            <td class="px-3 py-4">@include('user-web.partials.portal-badge', ['label' => $paymentLabels[$order['payment_status']] ?? $order['payment_status']])</td>
                            <td class="px-3 py-4">@include('user-web.partials.portal-badge', ['tone' => $deliveryTones[$order['delivery_status']] ?? 'neutral', 'label' => $deliveryLabels[$order['delivery_status']] ?? $order['delivery_status']])</td>
                            <td class="px-3 py-4 text-right">
                                <a class="inline-flex rounded-full px-4 py-2 text-sm font-semibold text-primary hover:bg-primary/10" href="{{ route('user-web.warehouse.supplier-orders', array_merge(request()->query(), ['selected' => $order['id']])) }}">Chọn</a>
                                <details class="text-left">
                                    <summary class="cursor-pointer list-none rounded-full px-4 py-2 text-sm font-semibold text-primary hover:bg-primary/10">Xem chi tiết</summary>
                                    <div class="mt-4 grid gap-4 rounded-3xl bg-surface-container-low p-4 sm:grid-cols-2">
                                        <div>
                                            <p class="text-on-surface-variant">Nhà cung cấp</p>
                                            <p class="mt-2 font-semibold">{{ $order['supplier_name'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Khách hàng</p>
                                            <p class="mt-2 font-semibold">{{ $order['customer_name'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Trạng thái</p>
                                            <p class="mt-2 font-semibold">{{ $deliveryLabels[$order['delivery_status']] ?? $order['delivery_status'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Địa chỉ</p>
                                            <p class="mt-2 font-semibold">{{ $order['address'] }}</p>
                                        </div>
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
                        <p class="text-xs uppercase tracking-widest text-on-surface-variant">Đơn đang chọn</p>
                        <h3 class="mt-2 font-headline text-2xl font-bold text-primary">Đơn #{{ $activeOrder['id'] }}</h3>
                        <p class="mt-1 text-sm text-on-surface-variant">{{ $activeOrder['supplier_name'] }} / {{ $activeOrder['customer_name'] }}</p>
                    </div>
                    @include('user-web.partials.portal-badge', ['tone' => $deliveryTones[$activeOrder['delivery_status']] ?? 'neutral', 'label' => $deliveryLabels[$activeOrder['delivery_status']] ?? $activeOrder['delivery_status']])
                </div>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                        <p class="text-on-surface-variant">Nhà cung cấp</p>
                        <p class="mt-2 font-semibold">{{ $activeOrder['supplier_name'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                        <p class="text-on-surface-variant">Khách hàng</p>
                        <p class="mt-2 font-semibold">{{ $activeOrder['customer_name'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                        <p class="text-on-surface-variant">Trạng thái</p>
                        <p class="mt-2 font-semibold">{{ $deliveryLabels[$activeOrder['delivery_status']] ?? $activeOrder['delivery_status'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-surface-container-low p-4 text-sm">
                        <p class="text-on-surface-variant">Địa chỉ</p>
                        <p class="mt-2 font-semibold">{{ $activeOrder['address'] }}</p>
                    </div>
                </div>
            </section>
        @endif

        @include('user-web.partials.pagination', ['paginator' => $orders])
    </div>
@endsection
