@if ($paginator->total() > 0)
    <nav class="table-pager" aria-label="ترقيم الصفحات">
        <ul class="pagination">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link page-nav" aria-hidden="true">السابق</span>
                @else
                    <a class="page-link page-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev">السابق</a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if ($paginator->hasMorePages())
                    <a class="page-link page-nav" href="{{ $paginator->nextPageUrl() }}" rel="next">التالي</a>
                @else
                    <span class="page-link page-nav" aria-hidden="true">التالي</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
