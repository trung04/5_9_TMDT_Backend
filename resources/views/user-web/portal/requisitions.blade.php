@extends('user-web.layouts.portal')

@section('title', $variant === 'supplier' ? 'Phiếu yêu cầu nhà cung cấp' : 'Phiếu tái nhập kho')

@section('content')
    @php
        $labels = ['submitted' => 'Đã gửi', 'approved' => 'Đã duyệt', 'received' => 'Đã nhận', 'cancelled' => 'Đã hủy'];
        $title = $variant === 'supplier' ? 'Phiếu yêu cầu nhà cung cấp' : 'Phiếu tái nhập kho';
        $description = $variant === 'supplier'
            ? 'Theo dõi yêu cầu bổ sung hàng, phê duyệt và xác nhận luân chuyển tồn kho.'
            : 'Duyệt nội bộ, hủy hoặc xác nhận đã nhập kho cho các phiếu đang luân chuyển.';
    @endphp

    <div class="space-y-8">
        @include('user-web.partials.portal-page-header', ['title' => $title, 'description' => $description])

        <div class="space-y-4">
            @forelse($requisitions as $requisition)
                <article class="space-y-4 rounded-[2rem] bg-white p-6 shadow-ambient">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-widest text-primary">#{{ $requisition['id'] }}</p>
                            <h3 class="mt-2 font-headline text-2xl font-semibold">{{ $requisition['inventory_sku'] }}</h3>
                            <p class="mt-1 text-sm text-on-surface-variant">{{ $requisition['product_name'] ?: 'Sản phẩm #'.$requisition['product_id'] }}</p>
                        </div>
                        @include('user-web.partials.portal-badge', ['label' => $labels[$requisition['status']] ?? $requisition['status']])
                    </div>

                    <div class="grid gap-4 text-sm md:grid-cols-3">
                        <div class="rounded-3xl bg-surface-container-low p-4">
                            <p class="text-on-surface-variant">Số lượng yêu cầu</p>
                            <p class="mt-2 font-semibold">{{ $requisition['requested_qty'] }}</p>
                        </div>
                        <div class="rounded-3xl bg-surface-container-low p-4">
                            <p class="text-on-surface-variant">Số lượng duyệt</p>
                            <p class="mt-2 font-semibold">{{ $requisition['approved_qty'] ?? ($variant === 'supplier' ? 'Chưa duyệt' : 'Chưa có') }}</p>
                        </div>
                        <div class="rounded-3xl bg-surface-container-low p-4">
                            <p class="text-on-surface-variant">ETA</p>
                            <p class="mt-2 font-semibold">{{ $requisition['eta_days'] }} ngày</p>
                        </div>
                    </div>

                    @if($requisition['note'])
                        <p class="rounded-3xl bg-surface-container-low p-4 text-sm text-on-surface-variant">{{ $requisition['note'] }}</p>
                    @endif

                    <div class="flex flex-wrap gap-3">
                        <form action="{{ route('user-web.operations.requisitions.status', $requisition['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button class="rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary" type="submit">
                                {{ $variant === 'supplier' ? 'Duyệt phiếu' : 'Duyệt nội bộ' }}
                            </button>
                        </form>
                        <form action="{{ route('user-web.operations.requisitions.status', $requisition['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="received">
                            <button class="rounded-full bg-surface-container px-5 py-2.5 text-sm font-semibold text-on-surface-variant" type="submit">
                                {{ $variant === 'supplier' ? 'Xác nhận đã giao' : 'Xác nhận đã nhập' }}
                            </button>
                        </form>
                        <form action="{{ route('user-web.operations.requisitions.status', $requisition['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button class="rounded-full px-5 py-2.5 text-sm font-semibold text-error hover:bg-error/10" type="submit" data-confirm="Hủy phiếu này?">Hủy phiếu</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-[2rem] bg-white p-8 text-center text-on-surface-variant shadow-ambient">
                    Chưa có phiếu nào.
                </div>
            @endforelse
        </div>

        @include('user-web.partials.pagination', ['paginator' => $requisitions])
    </div>
@endsection
