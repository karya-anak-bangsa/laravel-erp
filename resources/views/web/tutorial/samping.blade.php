{{--
    Kolom samping halaman tutorial: Daftar materi (hanya di halaman materi; materi yang sedang dibuka ditandai) →
    Konsultasi → Layanan kami → Kategori (daftar, kategori, dan halaman tutorial; pilihan pemilik 2026-10-20, sama dengan
    kolom samping artikel). Kategori hanya yang punya tutorial tayang; di halaman kategori, kategori yang sedang dibuka
    ditandai.
    Pakai: @include('web.tutorial.samping', ['materiAktif' => $materiTutorial]) atau
    @include('web.tutorial.samping', ['kategoriAktif' => $kategoriTutorial]) — $layanan dari controller; $daftarKategori di
    daftar, kategori, dan halaman tutorial; di halaman materi juga $tutorial dan $daftarMateri.
--}}
<aside class="kolom-samping" aria-label="Informasi tambahan">
    @isset($materiAktif)
        <nav class="kotak-samping" aria-labelledby="judul-materi-samping">
            <h2 id="judul-materi-samping" class="judul-samping">Daftar materi</h2>
            @include('web.tutorial.daftar-materi', ['kelas' => 'daftar-materi daftar-materi-samping'])
        </nav>
    @endisset

    @include('web.samping.konsultasi')

    @include('web.samping.layanan')

    @if (isset($daftarKategori) && $daftarKategori->isNotEmpty())
        <nav class="kotak-samping" aria-labelledby="judul-kategori-samping">
            <h2 id="judul-kategori-samping" class="judul-samping">Kategori</h2>
            <ul class="daftar-tautan-samping">
                @foreach ($daftarKategori as $item)
                    <li>
                        <a href="{{ route('kategori-tutorial.show', $item) }}" class="tautan-samping" @if (isset($kategoriAktif) && $kategoriAktif->is($item)) aria-current="page" @endif>
                            <span>{{ $item->nama_kategori }}</span>
                            <span class="jumlah-kategori">{{ $item->tutorial_tayang_count }}<span class="sr-only"> tutorial</span></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif
</aside>
