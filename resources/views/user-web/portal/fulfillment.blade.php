@extends('user-web.layouts.portal')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Fulfillment kho')

@section('content')
    @php
        $fulfillmentLabels = ['picking' => 'Đang lấy hàng', 'packing' => 'Đang đóng gói', 'awaiting_pickup' => 'Chờ đơn vị vận chuyển', 'shipped' => 'Đã gửi hàng'];
        $shippingLabels = ['standard' => 'Tiêu chuẩn', 'express' => 'Nhanh', 'priority' => 'Ưu tiên'];
        $nextStatusLabels = ['picking' => 'Chuyển sang đóng gói', 'packing' => 'Chuyển sang chờ lấy hàng', 'awaiting_pickup' => 'Xác nhận đã bàn giao vận chuyển', 'shipped' => 'Đã hoàn tất luồng kho'];
        $statusTone = fn (string $status): string => $status === 'shipped' ? 'success' : ($status === 'awaiting_pickup' ? 'warning' : 'primary');
    @endphp

    <div class="space-y-8">
        @include('user-web.partials.portal-page-header', [
            'title' => 'Fulfillment kho',
            'description' => 'Theo dõi picking, packing, bàn giao và nhịp xử lý đơn trong kho.',
        ])

        <section class="grid gap-6 xl:grid-cols-3">
            @include('user-web.partials.portal-stat-card', [
                'label' => 'Đơn đang xử lý',
                'value' => $stats['pending'],
                'icon' => 'local_shipping',
                'tone' => 'primary',
                'delta' => $stats['rush'].' đơn gấp',
                'helper' => 'vẫn đang nằm trong hàng đợi kho',
            ])
            @include('user-web.partials.portal-stat-card', [
                'label' => 'Khu đóng gói hoạt động',
                'value' => $stats['zones'],
                'icon' => 'warehouse',
                'tone' => 'secondary',
                'helper' => 'số khu đang có nhiệm vụ mở',
            ])
            @include('user-web.partials.portal-stat-card', [
                'label' => 'Đơn đã bàn giao',
                'value' => $stats['shipped'],
                'icon' => 'deployed_code',
                'tone' => 'tertiary',
                'helper' => 'đã chuyển sang trạng thái in_transit',
            ])
        </section>

        <section class="grid gap-6 xl:grid-cols-[2fr_1fr]">
            <div class="space-y-4 rounded-[2rem] bg-white p-6 shadow-ambient">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-headline text-2xl font-bold text-on-surface">Hàng đợi xử lý đang hoạt động</h3>
                        <p class="text-sm text-on-surface-variant">Ưu tiên đơn gấp nhưng vẫn giữ nhịp xử lý đều cho luồng tiêu chuẩn.</p>
                    </div>
                    @include('user-web.partials.portal-badge', ['tone' => 'primary', 'label' => $tasks->count().' nhiệm vụ trực tiếp'])
                </div>

                <div class="space-y-3">
                    @forelse($tasks as $task)
                        <a class="flex w-full flex-col gap-4 rounded-3xl border border-transparent bg-surface-container-low p-5 text-left transition hover:bg-surface-container {{ ($selectedTaskId ?? null) === $task['id'] ? 'border-primary/20 bg-primary/5' : '' }}" href="{{ route('user-web.warehouse.fulfillment', array_merge(request()->query(), ['selected' => $task['id']])) }}">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-widest text-on-surface-variant">{{ $task['order_id'] }}</p>
                                    <h4 class="font-headline text-xl font-semibold text-on-surface">{{ $task['customer_name'] }}</h4>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @include('user-web.partials.portal-badge', ['tone' => $task['priority'] === 'rush' ? 'danger' : 'neutral', 'label' => $task['priority'] === 'rush' ? 'Gấp' : 'Tiêu chuẩn'])
                                    @include('user-web.partials.portal-badge', ['tone' => $statusTone($task['status']), 'label' => $fulfillmentLabels[$task['status']] ?? $task['status']])
                                </div>
                            </div>
                            <div class="grid gap-3 text-sm text-on-surface-variant sm:grid-cols-3">
                                <div class="flex items-center gap-2">
                                    @include('user-web.partials.icon', ['name' => 'deployed_code_history', 'class' => 'text-primary'])
                                    <span>{{ $task['assigned_zone'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @include('user-web.partials.icon', ['name' => 'schedule', 'class' => 'text-primary'])
                                    <span>{{ $task['eta_label'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @include('user-web.partials.icon', ['name' => 'local_shipping', 'class' => 'text-primary'])
                                    <span>{{ $shippingLabels[$task['shipping_tier']] ?? $task['shipping_tier'] }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-[2rem] bg-surface-container-low p-8 text-center text-on-surface-variant">Chưa có task fulfillment.</div>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4 rounded-[2rem] bg-surface-container-low p-6 shadow-ambient">
                <h3 class="font-headline text-lg font-bold text-on-surface">Luồng hoàn tất đơn trực tiếp</h3>
                <div class="space-y-3">
                    @forelse($tasks as $task)
                        <div class="rounded-2xl bg-surface-container-lowest p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="font-semibold text-on-surface">{{ $task['order_id'] }}</p>
                                @include('user-web.partials.portal-badge', ['tone' => $statusTone($task['status']), 'label' => $fulfillmentLabels[$task['status']] ?? $task['status']])
                            </div>
                            <p class="text-sm text-on-surface-variant">{{ $task['customer_name'] }} · {{ $task['assigned_zone'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-on-surface-variant">Chưa có dữ liệu luồng kho.</p>
                    @endforelse
                </div>
            </div>
        </section>

        @if($activeTask)
            <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <div class="space-y-4 rounded-[2rem] bg-white p-6 shadow-ambient">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-on-surface-variant">Nhiệm vụ đang chọn</p>
                        <h2 class="mt-2 font-headline text-2xl font-bold">{{ $activeTask['order_id'] }}</h2>
                    </div>
                    <div class="space-y-3 text-sm text-on-surface-variant">
                        <p>Khách hàng: {{ $activeTask['customer_name'] }}</p>
                        <p>Khu xử lý: {{ $activeTask['assigned_zone'] }}</p>
                        <p>Gói giao hàng: {{ $shippingLabels[$activeTask['shipping_tier']] ?? $activeTask['shipping_tier'] }}</p>
                        <p>Trạng thái: {{ $fulfillmentLabels[$activeTask['status']] ?? $activeTask['status'] }}</p>
                        @if($relatedOrder)
                            <p>Địa chỉ nhận: {{ $relatedOrder['address'] }}</p>
                        @endif
                    </div>
                    <form action="{{ route('user-web.operations.fulfillment.advance', $activeTask['order_id']) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="note" value="Cập nhật từ màn hình fulfillment.">
                        <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-on-primary disabled:opacity-50" type="submit" @disabled($activeTask['status'] === 'shipped')>
                            {{ $nextStatusLabels[$activeTask['status']] ?? 'Cập nhật' }}
                        </button>
                    </form>
                </div>

                <div class="space-y-6 rounded-[2rem] bg-white p-6 shadow-ambient">
                    <div>
                        <h2 class="font-headline text-2xl font-bold">Nhịp bàn giao kho</h2>
                        <p class="mt-4 max-w-2xl text-sm leading-6 text-on-surface-variant">
                            Gom đơn tiêu chuẩn và đơn gấp theo từng cửa sổ xuất hàng, đồng thời đồng bộ trạng thái về logistics khi nhân viên kho xác nhận bước tiếp theo.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-xs uppercase tracking-widest text-on-surface-variant">Chuyến xe kế tiếp</p>
                            <p class="mt-2 font-headline text-2xl font-bold">10:30</p>
                        </div>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-xs uppercase tracking-widest text-on-surface-variant">Nhãn vận đơn</p>
                            <p class="mt-2 font-headline text-2xl font-bold">{{ $tasks->where('status', '!=', 'picking')->count() }}</p>
                        </div>
                        <div class="rounded-3xl bg-surface-container-low p-5">
                            <p class="text-xs uppercase tracking-widest text-on-surface-variant">Cảnh báo ưu tiên</p>
                            <p class="mt-2 font-headline text-2xl font-bold">{{ $tasks->where('priority', 'rush')->count() }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 rounded-3xl bg-surface-container-low p-5">
                        <p class="text-xs uppercase tracking-widest text-on-surface-variant">Lịch sử trạng thái</p>
                        @forelse($activeTask['status_history'] as $event)
                            <div class="rounded-2xl bg-surface-container-highest p-4 text-sm">
                                <p class="font-semibold text-on-surface">{{ $event['label'] }}</p>
                                <p class="mt-1 text-on-surface-variant">{{ $event['actor'] }} · {{ $ui->date($event['created_at']) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-on-surface-variant">Chưa có lịch sử.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        @endif

        @include('user-web.partials.pagination', ['paginator' => $tasks])
    </div>
@endsection
