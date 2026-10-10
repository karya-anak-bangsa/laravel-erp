@use('App\Support\TeksHtml')
@use('Illuminate\Support\Str')

<section id="artikel" class="seksi" aria-labelledby="judul-artikel">
    <div class="wadah">
        {{-- Tombol "Lihat semua artikel" & tautan kartu ditambahkan begitu halaman artikel tersedia. --}}
        <div class="kepala-artikel">
            <x-web.kepala-seksi id="judul-artikel" :nomor="$nomorSeksi('artikel')" label="Artikel" judul="Wawasan & kabar terbaru" rata="tengah">
                Tips, panduan, dan cerita seputar teknologi, pengembangan aplikasi, serta pelatihan IT.
            </x-web.kepala-seksi>
        </div>

        {{-- Markup tiap item identik: Full Color menjadikan item pertama kartu utama, Monochrome menyusunnya sebagai daftar editorial. --}}
        <div class="daftar-artikel">
            @foreach ($artikel as $item)
                <article class="kartu-artikel">
                    <div class="kartu-artikel-gambar"><img src="{{ $item->gambar_url }}" width="800" height="600" loading="lazy" decoding="async" alt="{{ $item->judul }}" class="gambar-konten"></div>
                    <div class="kartu-artikel-isi">
                        <div class="kartu-artikel-meta">
                            @if ($item->kategoriArtikel)
                                <span class="lencana-artikel">{{ $item->kategoriArtikel->nama_kategori }}</span>
                            @endif
                            {{-- Full Color: "Diterbitkan …" di bawah ringkasan (CSS); Monochrome: tanggal saja di kolom meta. Tanggal tidak
                                 dipatah agar di kartu sempit "Diterbitkan" yang turun baris. Halaman publik tanpa nol di depan tanggal ("9 Oktober"). --}}
                            <time class="tanggal-artikel" datetime="{{ $item->tanggal->toDateString() }}"><span class="mono:hidden">Diterbitkan </span><span class="whitespace-nowrap">{{ $item->tanggal->translatedFormat('j F Y') }}</span></time>
                        </div>
                        <h3 class="kartu-artikel-judul">{{ $item->judul }}</h3>
                        <p class="kartu-artikel-ringkasan">{{ Str::limit(TeksHtml::ringkasan($item->deskripsi), 160) }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
