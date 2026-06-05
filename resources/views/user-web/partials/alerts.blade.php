<div class="fixed left-1/2 top-20 z-[60] w-[calc(100%-2rem)] max-w-xl -translate-x-1/2 space-y-3">
    @if(session('status'))
        <div class="rounded-3xl border border-primary/20 bg-white px-5 py-4 text-sm font-medium text-primary shadow-ambient">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-3xl border border-error/20 bg-white px-5 py-4 text-sm font-medium text-error shadow-ambient">
            {{ $errors->first() }}
        </div>
    @endif
</div>
