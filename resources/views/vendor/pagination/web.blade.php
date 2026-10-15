{{--
    Paginasi frontend publik (<x-web.paginasi>), pola shadcn: nomor halaman ghost, halaman aktif outline. Tombol berteks
    tanpa ikon (pilihan pemilik). Ponsel: Sebelumnya · "Halaman 2 dari 3" · Berikutnya; nomor mulai 640px.
--}}
@if ($paginator->hasPages())
    <nav class="paginasi" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="btn" data-variant="ghost" aria-disabled="true">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn" data-variant="ghost">Sebelumnya</a>
        @endif

        <ul class="paginasi-nomor">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="paginasi-celah" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $halaman => $url)
                        <li>
                            @if ($halaman == $paginator->currentPage())
                                <span class="btn" data-variant="outline" aria-current="page"><span class="sr-only">Halaman </span>{{ $halaman }}</span>
                            @else
                                <a href="{{ $url }}" class="btn" data-variant="ghost"><span class="sr-only">Halaman </span>{{ $halaman }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>
        <p class="paginasi-ringkas">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</p>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn" data-variant="ghost">Berikutnya</a>
        @else
            <span class="btn" data-variant="ghost" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif
