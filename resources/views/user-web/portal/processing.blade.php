@extends('user-web.layouts.portal')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Xử lý đơn nhà cung cấp')

@section('content')
    @php
        $deliveryLabels = [
            'processing' => 'Đang xử lý',
            'ready_to_ship' => 'Sẵn sàng giao',
            'in_transit' => 'Đang vận chuyển',
            'delivered' => 'Đã giao',
            'disputed' => 'Có khiếu nại',
        ];
        $deliveryTones = [
            'delivered' => 'success',
            'ready_to_ship' => 'warning',
            'in_transit' => 'warning',
            'disputed' => 'danger',
            'processing' => 'primary',
        ];
    @endphp

    <div class="space-y-8">
        @include('user-web.partials.portal-page-header', [
            'title' => 'Xử lý đơn nhà cung cấp',
            'description' => 'Kiểm tra đơn cần chuẩn bị, chuyển sang sẵn sàng giao và xác nhận hoàn tất.',
        ])

        <section class="rounded-[2rem] bg-white shadow-ambient">
            <div class="border-b border-outline-variant/15 px-6 py-5">
                <h3 class="font-headline text-xl font-semibold">Nhà cung cấp xử lý đơn</h3>
            </div>
            <div class="overflow-x-auto p-6">
                <table class="min-w-[900px] w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-widest text-on-surface-variant">
                    <tr>
                        <th class="px-3 py-3">Đơn hàng</th>
                        <th class="px-3 py-3">Khách hàng</th>
                        <th class="px-3 py-3">Ngày</th>
                        <th class="px-3 py-3">Tổng tiền</th>
                        <th class="px-3 py-3">Trạng thái</th>
                        <th class="px-3 py-3 text-right">Thao tác</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/15">
                    @forelse($orders as $order)
                        <tr class="align-top hover:bg-primary/5">
                            <td class="px-3 py-4 font-semibold">#{{ $order['id'] }}</td>
                            <td class="px-3 py-4">
                                <p class="font-medium">{{ $order['customer_name'] }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $order['supplier_name'] }}</p>
                            </td>
                            <td class="px-3 py-4 text-on-surface-variant">{{ $ui->date($order['date']) }}</td>
                            <td class="px-3 py-4 font-semibold">{{ $ui->money($order['total']) }}</td>
                            <td class="px-3 py-4">
                                @include('user-web.partials.portal-badge', [
                                    'tone' => $deliveryTones[$order['delivery_status']] ?? 'neutral',
                                    'label' => $deliveryLabels[$order['delivery_status']] ?? $order['delivery_status'],
                                ])
                            </td>
                            <td class="px-3 py-4">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <form action="{{ route('user-web.operations.orders.delivery-status', $order['id']) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="delivery_status" value="ready_to_ship">
                                        <input type="hidden" name="note" value="Nhà cung cấp đã đánh dấu đơn sẵn sàng giao">
                                        <button class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-on-primary" type="submit">Sẵn sàng giao</button>
                                    </form>
                                    <form action="{{ route('user-web.operations.orders.delivery-status', $order['id']) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="delivery_status" value="delivered">
                                        <input type="hidden" name="note" value="Nhà cung cấp đã xác nhận giao hàng">
                                        <button class="rounded-full bg-surface-container px-4 py-2 text-xs font-semibold text-on-surface-variant" type="submit">Đã giao</button>
                                    </form>
                                </div>
                                <details class="mt-3 text-left">
                                    <summary class="cursor-pointer list-none text-sm font-semibold text-primary hover:underline">Chi tiết</summary>
                                    <div class="mt-3 grid gap-3 rounded-3xl bg-surface-container-low p-4 sm:grid-cols-2">
                                        <div>
                                            <p class="text-on-surface-variant">Khách hàng</p>
                                            <p class="mt-1 font-semibold">{{ $order['customer_name'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Nhà cung cấp</p>
                                            <p class="mt-1 font-semibold">{{ $order['supplier_name'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Tổng tiền</p>
                                            <p class="mt-1 font-semibold">{{ $ui->money($order['total']) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-on-surface-variant">Trạng thái hiện tại</p>
                                            <p class="mt-1 font-semibold">{{ $deliveryLabels[$order['delivery_status']] ?? $order['delivery_status'] }}</p>
                                        </div>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-3 py-8 text-center text-on-surface-variant" colspan="6">Chưa có đơn hàng.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @include('user-web.partials.pagination', ['paginator' => $orders])
    </div>
@endsection
