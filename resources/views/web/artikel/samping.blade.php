{{--
    Kolom samping halaman artikel (pilihan pemilik 2026-10-15): di samping isi mulai 1024px, di bawahnya turun ke bawah
    isi. Urutan kotak: Konsultasi → Layanan kami → Kategori → Artikel terbaru (hanya di detail artikel). Kategori hanya
    yang punya artikel terbit; di halaman kategori, kategori yang sedang dibuka ditandai. Kartu konsultasi menjadi slot
    promosi sementara dan hanya tampil mulai 1024px, karena di bawahnya langsung disusul band ajakan yang sama tujuannya.
    Pakai: @include('web.artikel.samping', ['artikelTerbaru' => $artikelTerbaru, 'kategoriAktif' => $kategoriArtikel])
    — $daftarKategori dan $layanan dari controller.
--}}
<aside class="kolom-samping" aria-label="Informasi tambahan">
    <div class="ajakan-samping dark">
        <div class="ajakan-blob -top-20 -left-16 size-56 bg-(--warna-aksen)/40 mono:hidden" aria-hidden="true"></div>
        <div class="ajakan-blob -right-20 -bottom-24 size-64 bg-sky-500/25 mono:hidden" aria-hidden="true"></div>
        <div class="ajakan-titik mono:hidden" aria-hidden="true"></div>

        <h2 class="judul-ajakan-samping">Konsultasi gratis</h2>
        <p class="deskripsi-ajakan-samping">Diskusikan kebutuhan website, aplikasi, atau pelatihan IT tim Anda bersama kami, tanpa biaya.</p>
        {{-- Sama dengan band ajakan: WhatsApp, atau formulir di beranda bila link WhatsApp di Identitas kosong. --}}
        @if (filled($identitas->link_whatsapp))
            <x-web.tautan-luar :href="$identitas->link_whatsapp" class="btn btn-besar">
                <x-web.ikon nama="whatsapp" class="size-5" />
                Chat via WhatsApp
            </x-web.tautan-luar>
        @else
            <a href="{{ route('beranda') }}#kontak" class="btn btn-besar">Kirim Pesan</a>
        @endif
    </div>

    @if ($layanan->isNotEmpty())
        <nav class="kotak-samping" aria-labelledby="judul-layanan-kami">
            <h2 id="judul-layanan-kami" class="judul-samping">Layanan kami</h2>
            <ul class="daftar-tautan-samping">
                @foreach ($layanan as $item)
                    {{-- Ke kartu layanannya di beranda, sama dengan kolom Layanan di footer. --}}
                    <li>
                        <a href="{{ route('beranda') }}#layanan-{{ $item->id_layanan }}" class="tautan-samping">
                            <span>{{ $item->judul }}</span>
                            <x-web.ikon nama="panah-kanan" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif

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
