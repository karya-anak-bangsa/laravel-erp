{{--
    Paginasi panel admin di kaki kartu daftar (<x-admin.pagination>): info jumlah data di kiri, tombol halaman di kanan.
    Di ponsel (≤768px) info di baris sendiri dan nomor halaman diganti "Halaman 2 dari 7" di antara ‹ ›, karena hingga
    11 tombol tidak muat selebar layar (pola paginasi publik).
--}}
@if ($paginator->total() > 0)
    <nav class="pagination" aria-label="Navigasi halaman">
        <span class="page-info">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </span>

        @if ($paginator->hasPages())
            <div class="page-nav">
                @if ($paginator->onFirstPage())
                    <span class="page-link disabled" aria-disabled="true" aria-label="Sebelumnya">‹</span>
                @else
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">‹</a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="page-link page-nomor disabled" aria-disabled="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $halaman => $url)
                            @if ($halaman == $paginator->currentPage())
                                <span class="page-link page-nomor active" aria-current="page">{{ $halaman }}</span>
                            @else
                                <a class="page-link page-nomor" href="{{ $url }}" aria-label="Halaman {{ $halaman }}">{{ $halaman }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                <span class="page-ringkas">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

                @if ($paginator->hasMorePages())
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">›</a>
                @else
                    <span class="page-link disabled" aria-disabled="true" aria-label="Berikutnya">›</span>
                @endif
            </div>
        @endif
    </nav>
@endif
