@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Khiếu nại - Heritage Harvest')

@section('content')
<section class="px-6 pb-20 pt-28">
    <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[300px_1fr]">
        @include('user-web.partials.account-sidebar')
        <div class="space-y-6">
            <div class="rounded-[2rem] bg-white p-6 shadow-ambient">
                <h1 class="text-3xl font-black text-green-950">Khiếu nại & hỗ trợ đơn</h1>
                <form class="mt-6 grid gap-4 sm:grid-cols-2" action="{{ route('user-web.account.disputes.store') }}" method="POST">
                    @csrf
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Đơn hàng</span>
                        <select class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="order_id">
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}">{{ $order->order_no }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Sản phẩm ID</span>
                        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="product_id" placeholder="Nhập product ID trong đơn">
                    </label>
                    <label class="space-y-2 sm:col-span-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Lý do</span>
                        <input class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="reason">
                    </label>
                    <label class="space-y-2 sm:col-span-2">
                        <span class="text-sm font-semibold text-on-surface-variant">Nội dung</span>
                        <textarea class="min-h-28 w-full rounded-2xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 outline-none" name="content"></textarea>
                    </label>
                    <button class="w-max rounded-full bg-primary px-6 py-3 text-sm font-semibold text-on-primary" type="submit">Gửi khiếu nại</button>
                </form>
            </div>

            <div class="space-y-4">
                @forelse($complaints as $complaint)
                    <article class="rounded-[2rem] bg-white p-5 shadow-ambient">
                        <div class="flex justify-between gap-3">
                            <div>
                                <p class="font-black text-green-950">#{{ $complaint['id'] }} · {{ $complaint['reason'] }}</p>
                                <p class="mt-2 text-sm text-on-surface-variant">{{ $complaint['content'] }}</p>
                            </div>
                            <span class="h-max rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $ui->complaintStatusLabel($complaint['status'] ?? null) }}</span>
                        </div>
                        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                            <div class="rounded-2xl bg-surface-container-low p-4">
                                <p class="text-on-surface-variant">Đơn hàng</p>
                                <p class="mt-1 font-semibold">{{ $complaint['order']['order_no'] ?? $complaint['orderNo'] ?? '' }}</p>
                            </div>
                            <div class="rounded-2xl bg-surface-container-low p-4">
                                <p class="text-on-surface-variant">Sản phẩm</p>
                                <p class="mt-1 font-semibold">{{ $complaint['product']['name'] ?? $complaint['productName'] ?? 'Không rõ' }}</p>
                            </div>
                            <div class="rounded-2xl bg-surface-container-low p-4">
                                <p class="text-on-surface-variant">Ngày tạo</p>
                                <p class="mt-1 font-semibold">{{ $ui->date($complaint['created_at'] ?? $complaint['createdAt'] ?? null) }}</p>
                            </div>
                        </div>
                        @if(! empty($complaint['resolution_note']))
                            <div class="mt-4 rounded-3xl bg-on-primary-container p-4 text-sm text-primary">Ghi chú xử lý: {{ $complaint['resolution_note'] }}</div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-[2rem] bg-white p-6 text-sm text-on-surface-variant shadow-ambient">Chưa có khiếu nại.</div>
                @endforelse
                @include('user-web.partials.pagination', ['paginator' => $complaints])
            </div>
        </div>
    </div>
</section>
@endsection
