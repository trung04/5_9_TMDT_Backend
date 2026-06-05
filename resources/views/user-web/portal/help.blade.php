@extends('user-web.layouts.portal')
@inject('ui', 'App\Support\UserWeb\UserWebPresenter')

@section('title', $variant === 'supplier' ? 'Hỗ trợ nhà cung cấp' : 'Hỗ trợ kho')

@section('content')
    @php
        $isSupplier = $variant === 'supplier';
        $defaultSubject = $isSupplier ? 'Cần xác nhận ETA với kho' : 'Cần bổ sung nhân sự ca tối';
        $defaultMessage = $isSupplier
            ? 'Nhờ đội điều phối kiểm tra lại lịch nhận hàng cho lô hàng tuần này.'
            : 'Khối lượng picking tăng nhanh, đề nghị điều phối thêm nhân sự cho ca 18h.';
    @endphp

    <div class="space-y-8">
        @include('user-web.partials.portal-page-header', [
            'title' => $isSupplier ? 'Hỗ trợ nhà cung cấp' : 'Hỗ trợ kho',
            'description' => $isSupplier
                ? 'Gửi yêu cầu hỗ trợ và theo dõi phản hồi cho luồng vận hành nhà cung cấp.'
                : 'Gửi yêu cầu hỗ trợ nội bộ và theo dõi phiếu vận hành kho.',
        ])

        <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <form class="space-y-4 rounded-[2rem] bg-white p-6 shadow-ambient" action="{{ route('user-web.support-tickets.store', $variant) }}" method="POST">
                @csrf
                <label class="block space-y-2 text-sm">
                    <span class="font-medium">Chủ đề</span>
                    <input class="w-full rounded-2xl bg-surface-container-highest px-4 py-3 outline-none focus:ring-2 focus:ring-primary/15" name="subject" value="{{ old('subject', $defaultSubject) }}">
                </label>
                <label class="block space-y-2 text-sm">
                    <span class="font-medium">Nội dung</span>
                    <textarea class="min-h-36 w-full rounded-2xl bg-surface-container-highest px-4 py-3 outline-none focus:ring-2 focus:ring-primary/15" name="message">{{ old('message', $defaultMessage) }}</textarea>
                </label>
                <button class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-on-primary" type="submit">Gửi phiếu hỗ trợ</button>
            </form>

            <section class="space-y-4 rounded-[2rem] bg-surface-container-low p-6 shadow-ambient">
                <h3 class="font-headline text-2xl font-bold">{{ $isSupplier ? 'Phiếu gần đây' : 'Danh sách phiếu hỗ trợ' }}</h3>
                @forelse($tickets as $ticket)
                    <article class="rounded-3xl bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-primary">#{{ $ticket->id }}</p>
                                <p class="mt-2 font-semibold">{{ $ticket->subject }}</p>
                            </div>
                            @if(! $isSupplier && $ticket->status !== \App\Models\SupportTicket::STATUS_RESOLVED)
                                <form action="{{ route('user-web.support-tickets.resolve', $ticket->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="text-sm font-medium text-primary hover:underline" type="submit">Đánh dấu đã xử lý</button>
                                </form>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-on-surface-variant">{{ $ticket->message }}</p>
                        <p class="mt-3 text-xs uppercase tracking-widest text-on-surface-variant">{{ $ui->supportTicketStatusLabel($ticket->status) }}</p>
                    </article>
                @empty
                    <p class="text-sm text-on-surface-variant">{{ $isSupplier ? 'Chưa có phiếu nào được tạo.' : 'Chưa có phiếu nào cho kho vận.' }}</p>
                @endforelse

                @include('user-web.partials.pagination', ['paginator' => $tickets])
            </section>
        </div>
    </div>
@endsection
