@if($paginator->hasPages())
    <div class="row between small" style="margin-top: 16px;">
        <div class="muted">
            Hiển thị {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }}
            trên tổng số {{ $paginator->total() }}
        </div>
        <div class="row">
            @if($paginator->onFirstPage())
                <span class="btn btn-secondary" style="opacity: 0.5; pointer-events: none;">Trang trước</span>
            @else
                <a class="btn btn-secondary" href="{{ $paginator->previousPageUrl() }}">Trang trước</a>
            @endif

            <span class="muted small">Trang {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if($paginator->hasMorePages())
                <a class="btn btn-secondary" href="{{ $paginator->nextPageUrl() }}">Trang sau</a>
            @else
                <span class="btn btn-secondary" style="opacity: 0.5; pointer-events: none;">Trang sau</span>
            @endif
        </div>
    </div>
@endif
