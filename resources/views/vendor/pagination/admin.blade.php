@if ($paginator->total() > 0)
    <nav class="pagination" aria-label="Navigasi halaman">
        <span class="page-info">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </span>

        @if ($paginator->hasPages())
            @if ($paginator->onFirstPage())
                <span class="page-link disabled" aria-disabled="true" aria-label="Sebelumnya">‹</span>
            @else
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-link disabled" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $halaman => $url)
                        @if ($halaman == $paginator->currentPage())
                            <span class="page-link active" aria-current="page">{{ $halaman }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}" aria-label="Halaman {{ $halaman }}">{{ $halaman }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">›</a>
            @else
                <span class="page-link disabled" aria-disabled="true" aria-label="Berikutnya">›</span>
            @endif
        @endif
    </nav>
@endif
