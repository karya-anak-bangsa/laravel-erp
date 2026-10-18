{{--
    Kolom samping halaman artikel (pilihan pemilik 2026-10-15): di samping isi mulai 1024px, di bawahnya turun ke bawah
    isi. Urutan kotak: Konsultasi → Layanan kami → Kategori → Artikel terbaru (hanya di detail artikel). Kategori hanya
    yang punya artikel terbit; di halaman kategori, kategori yang sedang dibuka ditandai. Kotak Konsultasi & Layanan kami
    dipakai bersama kolom samping portofolio (web/samping).
    Pakai: @include('web.artikel.samping', ['artikelTerbaru' => $artikelTerbaru, 'kategoriAktif' => $kategoriArtikel])
    — $daftarKategori dan $layanan dari controller.
--}}
<aside class="kolom-samping" aria-label="Informasi tambahan">
    @include('web.samping.konsultasi')

    @include('web.samping.layanan')

    @if ($daftarKategori->isNotEmpty())
        <nav class="kotak-samping" aria-labelledby="judul-kategori-samping">
            <h2 id="judul-kategori-samping" class="judul-samping">Kategori</h2>
            <ul class="daftar-tautan-samping">
                @foreach ($daftarKategori as $item)
                    <li>
                        <a href="{{ route('kategori-artikel.show', $item) }}" class="tautan-samping" @if (isset($kategoriAktif) && $kategoriAktif->is($item)) aria-current="page" @endif>
                            <span>{{ $item->nama_kategori }}</span>
                            <span class="jumlah-kategori">{{ $item->artikel_terbit_count }}<span class="sr-only"> artikel</span></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif

    @if (isset($artikelTerbaru) && $artikelTerbaru->isNotEmpty())
        <div class="kotak-samping">
            <h2 class="judul-samping">Artikel terbaru</h2>
            <ul class="daftar-terbaru">
                @foreach ($artikelTerbaru as $item)
                    <li>
                        {{-- Gambar tanpa alt: judul di sebelahnya sudah menjadi nama tautan. --}}
                        <a href="{{ route('artikel.show', $item->slug) }}" class="tautan-terbaru">
                            <span class="gambar-terbaru"><img src="{{ $item->gambar_url }}" width="800" height="600" loading="lazy" decoding="async" alt="" class="gambar-konten"></span>
                            <span class="isi-terbaru">
                                <span class="judul-terbaru">{{ $item->judul }}</span>
                                <time class="tanggal-terbaru" datetime="{{ $item->tanggal->toDateString() }}">{{ $item->tanggal->translatedFormat('j F Y') }}</time>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('artikel.index') }}" class="btn btn-besar tombol-semua-artikel">Lihat semua artikel</a>
        </div>
    @endif
</aside>
