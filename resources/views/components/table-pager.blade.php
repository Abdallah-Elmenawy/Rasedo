@props(['items'])

@if(is_object($items) && method_exists($items, 'total') && $items->total() > 0)
    <div class="card-footer table-card-footer">
        <span class="table-page-summary">عرض {{ $items->firstItem() }} - {{ $items->lastItem() }} من {{ $items->total() }}</span>
        {{ $items->onEachSide(1)->withQueryString()->links('pagination.rasedo') }}
    </div>
@endif
