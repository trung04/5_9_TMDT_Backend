@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Đơn hàng - Heritage Harvest')

@section('content')
@php
    $currentOrder = $activeOrder ?? $orders->first();
    $timeline = [
        \App\Models\Order::STATUS_PENDING => 'Chờ xác nhận',
        \App\Models\Order::STATUS_CONFIRMED => 'Đã xác nhận',
        \App\Models\Order::STATUS_PACKED => 'Đã đóng gói',
        \App\Models\Order::STATUS_SHIPPED => 'Đang giao',
        \App\Models\Order::STATUS_DELIVERED => 'Đã giao',
    ];
    $timelineKeys = array_keys($timeline);
    $statusFilter = $orderFilters['status'] ?? 'all';
    $searchQuery = $orderFilters['q'] ?? '';
@endphp

<section class="mx-auto flex max-w-screen-2xl flex-col gap-8 px-6 pb-16 pt-24 md:flex-row">
    <aside class="w-full md:w-80">
        @include('user-web.partials.account-sidebar')
    </aside>

    <div class="flex-1 space-y-8">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-primary">Đơn hàng</p>
                <h1 class="mt-2 font-headline text-2xl font-bold tracking-tight">Lịch sử đơn hàng</h1>
            </div>
            <button class="inline-flex items-center gap-2 self-start rounded-full border border-outline-variant/20 bg-surface-container-lowest px-5 py-2.5 text-sm font-semibold text-on-surface transition hover:border-primary/30 hover:text-primary sm:self-auto" type="button" data-toggle="#orders-filter-bar">
                @include('user-web.partials.icon', ['name' => 'filter_list', 'class' => 'text-sm'])
                Bộ lọc
            </button>
        </header>

        @if($orders->isEmpty() && $searchQuery === '' && $statusFilter === 'all')
            <div class="rounded-xl bg-surface-container-low p-10 text-center">
                <h2 class="font-headline text-3xl font-bold tracking-tight">Chưa có đơn hàng nào</h2>
                <p class="mt-3 text-on-surface-variant">Sau khi đặt hàng thành công, đơn sẽ xuất hiện tại đây và được cập nhật trạng thái mới nhất.</p>
                <a class="mt-6 inline-flex rounded-full bg-primary px-6 py-3 font-semibold text-on-primary" href="{{ route('user-web.products.index') }}">Mở cửa hàng</a>
            </div>
        @else
            <section class="overflow-hidden rounded-xl bg-surface-container-lowest">
                <form id="orders-filter-bar" class="border-b border-outline-variant/15 bg-gradient-to-r from-surface-container-lowest to-surface-container-low p-6" action="{{ route('user-web.account.orders') }}" method="GET">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <input class="w-full rounded-2xl border border-outline-variant/10 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-primary/30 focus:ring-2 focus:ring-primary/10" name="q" value="{{ $searchQuery }}" placeholder="Tìm theo mã đơn, người nhận hoặc trạng thái...">
                        <div class="flex min-w-[17rem] items-center gap-3 rounded-2xl border border-outline-variant/10 bg-white px-4 py-3 shadow-sm">
                            <span class="whitespace-nowrap text-sm font-semibold text-on-surface">Trạng thái</span>
                            <select class="min-w-0 flex-1 rounded-xl bg-surface-container-low px-3 py-2 text-sm font-medium text-on-surface outline-none" name="status">
                                <option value="all" @selected($statusFilter === 'all')>Tất cả</option>
                                @foreach($availableStatuses as $status)
                                    <option value="{{ $status }}" @selected($statusFilter === $status)>{{ $ui->orderStatusLabel($status) }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-on-primary" type="submit">Lọc</button>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead class="border-b border-outline-variant/15 bg-surface-container-low">
                            <tr>
                                @foreach(['Mã đơn', 'Ngày', 'Tổng tiền', 'Trạng thái', 'Thanh toán', 'Thao tác'] as $title)
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-on-surface-variant">{{ $title }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @forelse($orders as $order)
                                <tr class="transition-colors hover:bg-surface-container-low {{ $currentOrder && $currentOrder->id === $order->id ? 'bg-primary/5' : '' }}">
                                    <td class="px-6 py-5 text-sm font-bold">{{ $order->order_no }}</td>
                                    <td class="px-6 py-5 text-sm text-on-surface-variant">{{ $ui->date($order->created_at) }}</td>
                                    <td class="px-6 py-5 text-sm font-medium">{{ $ui->money($order->total_amount) }}</td>
                                    <td class="px-6 py-5 text-sm text-on-surface-variant">{{ $ui->orderStatusLabel($order->status) }}</td>
                                    <td class="px-6 py-5 text-sm text-on-surface-variant">{{ $order->payment ? $ui->paymentStatusLabel($order->payment->payment_status) : 'Chưa có' }}</td>
                                    <td class="px-6 py-5">
                                        <a class="rounded-full border border-outline-variant/20 px-4 py-2 text-sm font-semibold text-primary transition hover:border-primary/40" href="{{ route('user-web.account.orders.show', array_merge(request()->query(), ['order' => $order->id])) }}">
                                            Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-8 text-center text-sm text-on-surface-variant" colspan="6">Không tìm thấy đơn hàng phù hợp.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @include('user-web.partials.pagination', ['paginator' => $orders])

            @if($currentOrder)
                @php
                    $paymentPayload = $currentOrder->payment?->raw_payload ?? [];
                    $transferSubmitted = (bool) ($paymentPayload['customer_transfer_submitted'] ?? false);
                    $currentStatusIndex = array_search($currentOrder->status, $timelineKeys, true);
                    $canPayAgain = $currentOrder->status === \App\Models\Order::STATUS_CANCELLED
                        || $currentOrder->payment?->payment_status === \App\Models\Payment::STATUS_FAILED
                        || ($currentOrder->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_TRANSFER && $currentOrder->payment?->payment_status === \App\Models\Payment::STATUS_PENDING);
                @endphp
                <section class="rounded-[1.5rem] bg-white p-5 shadow-ambient">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.24em] text-primary">Chi tiết đơn hàng</p>
                            <h2 class="mt-2 text-lg font-semibold text-on-surface">Mã đơn {{ $currentOrder->order_no }}</h2>
                        </div>
                        <span class="rounded-full bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">{{ $ui->orderStatusLabel($currentOrder->status) }}</span>
                    </div>

                    <div class="mt-8 rounded-[1.75rem] border border-outline-variant/15 bg-surface-container-low p-6">
                        <div class="relative mx-auto flex w-full max-w-5xl flex-wrap items-start justify-between gap-6">
                            <div class="absolute left-0 right-0 top-5 hidden h-[2px] bg-outline-variant/20 md:block"></div>
                            @foreach($timeline as $step => $label)
                                @php
                                    $stepIndex = array_search($step, $timelineKeys, true);
                                    $isDone = $currentOrder->status === \App\Models\Order::STATUS_DELIVERED
                                        || ($currentStatusIndex !== false && $stepIndex !== false && $stepIndex <= $currentStatusIndex);
                                    $isCurrent = $currentOrder->status === \App\Models\Order::STATUS_PENDING && $step === \App\Models\Order::STATUS_PENDING;
                                @endphp
                                <div class="relative z-10 flex flex-1 flex-col items-center gap-3 text-center">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm font-bold {{ $isDone ? 'border-primary bg-primary text-on-primary' : ($isCurrent ? 'border-secondary bg-secondary text-on-secondary' : 'border-outline-variant/35 bg-white text-on-surface-variant') }}">
                                        {{ $isDone ? '✓' : $loop->iteration }}
                                    </div>
                                    <p class="mt-1 text-sm font-semibold text-on-surface">{{ $label }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($currentOrder->status === \App\Models\Order::STATUS_CANCELLED)
                        <div class="mt-4 rounded-2xl border border-error/20 bg-error-container/60 px-4 py-3 text-sm text-error">Đơn hàng đã hủy.</div>
                    @elseif($currentOrder->status === \App\Models\Order::STATUS_DELIVERY_FAILED)
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Giao hàng thất bại. Đơn đang chờ admin xử lý giao lại hoặc hủy.</div>
                    @elseif(in_array($currentOrder->status, [\App\Models\Order::STATUS_PACKED, \App\Models\Order::STATUS_SHIPPED], true))
                        <div class="mt-4 rounded-2xl border border-primary/15 bg-primary/5 px-4 py-3 text-sm text-on-surface">Đơn hàng đã được đóng gói hoặc đang giao, bạn không thể tự hủy. Vui lòng liên hệ hỗ trợ nếu cần xử lý.</div>
                    @endif

                    <div class="mt-6 grid gap-5 xl:grid-cols-3">
                        <div class="h-full rounded-[1.5rem] bg-surface-container-low p-5">
                            <p class="text-center text-xs uppercase tracking-[0.18em] text-on-surface-variant">Thông tin đơn hàng</p>
                            <div class="mt-4 grid gap-3 text-sm md:grid-cols-2 xl:grid-cols-1">
                                <p><span class="font-medium">Mã đơn:</span> {{ $currentOrder->order_no }}</p>
                                <p><span class="font-medium">Ngày đặt:</span> {{ $ui->date($currentOrder->created_at) }}</p>
                                <p><span class="font-medium">Trạng thái đơn:</span> {{ $ui->orderStatusLabel($currentOrder->status) }}</p>
                                <p><span class="font-medium">Trạng thái thanh toán:</span> {{ $currentOrder->payment ? $ui->paymentStatusLabel($currentOrder->payment->payment_status) : 'Chưa có' }}</p>
                                <p><span class="font-medium">Phương thức thanh toán:</span> {{ $ui->paymentMethodLabel($currentOrder->payment_method) }}</p>
                                <p><span class="font-medium">Đơn vị vận chuyển:</span> {{ $currentOrder->shipping_carrier ?: 'Chưa cập nhật' }}</p>
                                <p><span class="font-medium">Mã vận đơn:</span> {{ $currentOrder->shipping_code ?: 'Chưa tạo' }}</p>
                                <p><span class="font-medium">Địa chỉ nhận:</span> {{ $currentOrder->shipping_address }}</p>
                            </div>
                        </div>

                        <div class="h-full rounded-[1.5rem] bg-surface-container-low p-5">
                            <p class="text-center text-xs uppercase tracking-[0.18em] text-on-surface-variant">Sản phẩm trong đơn</p>
                            <div class="mt-4 space-y-3">
                                @foreach($currentOrder->items as $item)
                                    <div class="flex items-start justify-between gap-4 rounded-2xl bg-white px-4 py-4 shadow-sm">
                                        <div>
                                            <p class="font-semibold text-on-surface">{{ $item->product_name_snapshot }}</p>
                                            <p class="mt-1 text-sm text-on-surface-variant">Số lượng: {{ $item->quantity }}</p>
                                            <p class="text-sm text-on-surface-variant">Đơn giá: {{ $ui->money($item->unit_price) }}</p>
                                        </div>
                                        <span class="text-sm font-semibold text-primary">{{ $ui->money($item->line_total) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex h-full flex-col gap-4">
                            <div class="rounded-[1.5rem] bg-surface-container-low p-5">
                                <p class="text-center text-xs uppercase tracking-[0.18em] text-on-surface-variant">Tổng hợp thanh toán</p>
                                <div class="mt-4 space-y-3 text-sm">
                                    <div class="flex justify-between"><span class="text-on-surface-variant">Tạm tính</span><span>{{ $ui->money($currentOrder->subtotal) }}</span></div>
                                    <div class="flex justify-between"><span class="text-on-surface-variant">Phí vận chuyển</span><span>{{ $ui->money($currentOrder->shipping_fee) }}</span></div>
                                    <div class="flex justify-between"><span class="text-on-surface-variant">Giảm giá</span><span>{{ $ui->money($currentOrder->discount_amount) }}</span></div>
                                    <div class="flex justify-between"><span class="text-on-surface-variant">Tổng tiền</span><span class="font-semibold">{{ $ui->money($currentOrder->total_amount) }}</span></div>
                                </div>
                            </div>

                            @if($currentOrder->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_TRANSFER)
                                <div class="rounded-[1.5rem] border border-primary/20 bg-primary/5 p-5 text-sm">
                                    <p class="text-center text-xs uppercase tracking-[0.18em] text-on-surface-variant">Thông tin chuyển khoản</p>
                                    <div class="mt-3 space-y-2 text-on-surface-variant">
                                        <p>Ngân hàng: {{ $paymentPayload['bank_name'] ?? 'MB Bank' }}</p>
                                        <p>Chủ tài khoản: {{ $paymentPayload['account_name'] ?? 'HERITAGE HARVEST' }}</p>
                                        <p>Số tài khoản: {{ $paymentPayload['account_number'] ?? '0123456789' }}</p>
                                        <p>Nội dung chuyển khoản: {{ $paymentPayload['transfer_content'] ?? $currentOrder->order_no }}</p>
                                        <p>Khách đã báo chuyển khoản: {{ $transferSubmitted ? 'Đã báo' : 'Chưa báo' }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <form action="{{ route('user-web.account.orders.reorder', $currentOrder->id) }}" method="POST">
                            @csrf
                            <button class="rounded-full border border-outline-variant/20 px-4 py-2 text-sm font-medium" type="submit">Thêm lại vào giỏ</button>
                        </form>
                        @if(in_array($currentOrder->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_CONFIRMED], true))
                            <button class="rounded-full border border-error/25 px-4 py-2 text-sm font-semibold text-error" type="button" data-toggle="#cancel-order-panel">Hủy đơn</button>
                        @endif
                        @if($canPayAgain)
                            <form action="{{ route('user-web.account.orders.reorder', $currentOrder->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="go_to_checkout" value="1">
                                <button class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-on-primary" type="submit">Thanh toán lại</button>
                            </form>
                        @endif
                        @if($currentOrder->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_TRANSFER && $currentOrder->payment?->payment_status === \App\Models\Payment::STATUS_PENDING)
                            <form action="{{ route('user-web.account.orders.confirm-transfer', $currentOrder->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="rounded-full border border-primary/30 px-4 py-2 text-sm font-semibold text-primary disabled:opacity-50" type="submit" @disabled($transferSubmitted)>
                                    {{ $transferSubmitted ? 'Đã gửi xác nhận' : 'Tôi đã chuyển khoản' }}
                                </button>
                            </form>
                        @endif
                        @if($currentOrder->status === \App\Models\Order::STATUS_SHIPPED)
                            <form action="{{ route('user-web.account.orders.confirm-delivery', $currentOrder->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="rounded-full bg-secondary px-4 py-2 text-sm font-semibold text-on-secondary" type="submit">Đã nhận được hàng</button>
                            </form>
                        @endif
                    </div>

                    @if(in_array($currentOrder->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_CONFIRMED], true))
                        <form id="cancel-order-panel" class="mt-6 hidden rounded-[1.5rem] border border-error/20 bg-error/5 p-6" action="{{ route('user-web.account.orders.cancel', $currentOrder->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <h3 class="text-center text-xl font-semibold text-on-surface">Xác nhận hủy đơn</h3>
                            <p class="mt-3 text-center text-sm text-on-surface-variant">Bạn có chắc chắn muốn hủy đơn hàng này không? Sau khi hủy, đơn hàng sẽ không thể tiếp tục xử lý.</p>
                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-on-surface">Lý do hủy</span>
                                    <select class="w-full rounded-2xl border border-outline-variant/15 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-primary/15" name="reason">
                                        @foreach($cancelReasons as $reason)
                                            <option value="{{ $reason }}">{{ $reason }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-on-surface">Ghi chú</span>
                                    <textarea class="min-h-28 w-full rounded-2xl border border-outline-variant/15 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-primary/15" name="note" placeholder="Nhập lý do hủy đơn nếu cần"></textarea>
                                </label>
                            </div>
                            <div class="mt-6 flex flex-wrap justify-center gap-3">
                                <button class="rounded-full border border-outline-variant/20 px-5 py-2.5 text-sm font-medium" type="button" data-toggle="#cancel-order-panel">Không, quay lại</button>
                                <button class="rounded-full bg-error px-5 py-2.5 text-sm font-semibold text-white" type="submit">Xác nhận hủy</button>
                            </div>
                        </form>
                    @endif
                </section>
            @endif
        @endif
    </div>
</section>
@endsection
