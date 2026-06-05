@extends('user-web.layouts.storefront')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', 'Đặt hàng thành công - Heritage Harvest')

@section('content')
@php
    $paymentPayload = $order->payment?->raw_payload ?? [];
    $isBank = $order->payment_method === \App\Models\Order::PAYMENT_METHOD_BANK_TRANSFER;
@endphp
<section class="px-6 pb-20 pt-32">
    <div class="mx-auto max-w-4xl rounded-[2.5rem] bg-white p-8 text-center shadow-ambient sm:p-12">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 text-primary">
            @include('user-web.partials.icon', ['name' => 'check_circle', 'class' => 'text-5xl'])
        </div>
        <h1 class="mt-6 text-4xl font-black text-green-950">Đặt hàng thành công</h1>
        <p class="mt-3 text-on-surface-variant">Mã đơn {{ $order->order_no }} · {{ $ui->money($order->total_amount) }}</p>

        @if($isBank)
            <div class="mx-auto mt-8 max-w-2xl rounded-[2rem] bg-surface-container-low p-6 text-left">
                <h2 class="text-xl font-black text-green-950">Hướng dẫn chuyển khoản</h2>
                <div class="mt-5 grid gap-3 text-sm">
                    @foreach([
                        'Ngân hàng' => $paymentPayload['bank_name'] ?? 'MB Bank',
                        'Chủ tài khoản' => $paymentPayload['account_name'] ?? 'HERITAGE HARVEST',
                        'Số tài khoản' => $paymentPayload['account_number'] ?? '0123456789',
                        'Số tiền' => $ui->money($order->total_amount),
                        'Nội dung' => $paymentPayload['transfer_content'] ?? $order->order_no,
                    ] as $label => $value)
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-white p-4">
                            <div>
                                <p class="text-xs text-on-surface-variant">{{ $label }}</p>
                                <p class="font-bold">{{ $value }}</p>
                            </div>
                            <button class="rounded-full bg-primary/10 px-3 py-2 text-xs font-semibold text-primary" type="button" data-copy-value="{{ $value }}">Sao chép</button>
                        </div>
                    @endforeach
                </div>
                @if(! ($paymentPayload['customer_transfer_submitted'] ?? false))
                    <form class="mt-5 space-y-3" action="{{ route('user-web.checkout.confirm-transfer', $order->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input class="w-full rounded-2xl border-none bg-white px-4 py-3 text-sm outline-none" name="note" placeholder="Ghi chú chuyển khoản (nếu có)">
                        <button class="rounded-full bg-emerald-700 px-5 py-3 text-sm font-semibold text-white" type="submit">Tôi đã chuyển khoản</button>
                    </form>
                @else
                    <p class="mt-5 rounded-2xl bg-primary/10 p-4 text-sm font-semibold text-primary">Đã ghi nhận xác nhận chuyển khoản.</p>
                @endif
            </div>
        @endif

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a class="rounded-full bg-primary px-6 py-3 font-semibold text-on-primary" href="{{ route('user-web.account.orders.show', $order->id) }}">Xem đơn hàng</a>
            <a class="rounded-full bg-surface-container px-6 py-3 font-semibold text-on-surface-variant" href="{{ route('user-web.products.index') }}">Tiếp tục mua sắm</a>
        </div>
    </div>
</section>
@endsection
