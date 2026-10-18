{{--
    Kolom samping halaman portofolio, susunannya sama dengan kolom samping artikel: Konsultasi → Layanan kami →
    Portofolio lainnya (hanya di detail portofolio, tanpa portofolio yang sedang dibuka).
    Pakai: @include('web.portofolio.samping', ['portofolioLainnya' => $portofolioLainnya]) — $layanan dari controller.
--}}
<aside class="kolom-samping" aria-label="Informasi tambahan">
    @include('web.samping.konsultasi')

    @include('web.samping.layanan')

    @if (isset($portofolioLainnya) && $portofolioLainnya->isNotEmpty())
        <div class="kotak-samping">
            <h2 class="judul-samping">Portofolio lainnya</h2>
            {{-- Baris sama dengan Artikel terbaru; kategori menggantikan tanggal. --}}
            <ul class="daftar-terbaru">
                @foreach ($portofolioLainnya as $item)
                    <li>
                        {{-- Gambar tanpa alt: judul di sebelahnya sudah menjadi nama tautan. --}}
                        <a href="{{ route('portofolio.show', $item->slug) }}" class="tautan-terbaru">
                            <span class="gambar-terbaru"><img src="{{ $item->gambar_url }}" width="800" height="600" loading="lazy" decoding="async" alt="" class="gambar-konten"></span>
                            <span class="isi-terbaru">
                                <span class="judul-terbaru">{{ $item->judul }}</span>
                                <span class="kategori-terbaru">{{ $item->kategori }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('portofolio.index') }}" class="btn btn-besar tombol-semua-portofolio">Lihat semua portofolio</a>
        </div>
    @endif
</aside>
