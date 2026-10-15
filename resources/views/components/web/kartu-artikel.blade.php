{{--
    Kartu artikel (beranda dan daftar artikel). Markup identik di kedua tema; susunannya diatur CSS menurut wadahnya:
    .daftar-artikel (beranda: kartu utama + kartu samping) atau .grid-artikel (daftar artikel). Monochrome menyusunnya
    sebagai baris editorial. Judul h3 di bawah kepala seksi; h2 di halaman daftar (di bawah h1). :pita="true" (daftar
    artikel) menampilkan lencana kategori sebagai pita di tepi kanan kartu: Full Color di semua lebar, Monochrome mulai
    640px (di bawahnya tetap lencana di atas judul).
    Pakai: <x-web.kartu-artikel :artikel="$item" tag-judul="h2" :pita="true" />
--}}
@props(['artikel', 'tagJudul' => 'h3', 'pita' => false])

@use('App\Support\TeksHtml')

<article class="kartu-artikel" @if ($pita) data-pita @endif>
    <div class="kartu-artikel-gambar"><img src="{{ $artikel->gambar_url }}" width="800" height="600" loading="lazy" decoding="async" alt="{{ $artikel->judul }}" class="gambar-konten"></div>
    <div class="kartu-artikel-isi">
        <div class="kartu-artikel-meta">
            @if ($artikel->kategoriArtikel)
                <span class="lencana-artikel">{{ $artikel->kategoriArtikel->nama_kategori }}</span>
            @endif
            {{-- Full Color: "Diterbitkan …" di bawah ringkasan (CSS; kartu samping di bawah 1024px hanya tanggal); Monochrome: tanggal saja di
                 kolom meta. Tanggal tidak dipatah. Halaman publik tanpa nol di depan tanggal ("9 Oktober"). --}}
            <time class="tanggal-artikel" datetime="{{ $artikel->tanggal->toDateString() }}"><span class="awalan-tanggal mono:hidden">Diterbitkan </span><span class="whitespace-nowrap">{{ $artikel->tanggal->translatedFormat('j F Y') }}</span></time>
        </div>
        {{-- Tautan judul direntangkan (::after) sehingga seluruh kartu bisa diklik. --}}
        <{{ $tagJudul }} class="kartu-artikel-judul"><a href="{{ route('artikel.show', $artikel->slug) }}" class="tautan-rentang">{{ $artikel->judul }}</a></{{ $tagJudul }}>
        {{-- Maksimal 150 karakter termasuk spasi, dipotong di akhir kata (pilihan pemilik 2026-10-11). --}}
        <p class="kartu-artikel-ringkasan">{{ TeksHtml::potongDiAkhirKata(TeksHtml::ringkasan($artikel->deskripsi), 150) }}</p>
    </div>
</article>
