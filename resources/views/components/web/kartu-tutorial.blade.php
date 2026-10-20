{{--
    Kartu tutorial (daftar tutorial): susunan dan kelas kartu portofolio dipakai bersama, termasuk pita kategori
    dan corak warna Full Color per kategori, ditambah jumlah materi di bawah ringkasan. Judul h2 karena hanya dipakai di
    halaman daftar (di bawah h1).
    Pakai: <x-web.kartu-tutorial :tutorial="$item" /> — $item dari ->with('kategoriTutorial')->withCount('materiTutorial').
--}}
@props(['tutorial'])

@use('App\Support\AksenWarna')
@use('App\Support\TeksHtml')

<article class="kartu-portofolio {{ AksenWarna::untuk($tutorial->kategoriTutorial->nama_kategori) }}">
    <span class="pita-kategori">{{ $tutorial->kategoriTutorial->nama_kategori }}</span>
    <div class="kartu-portofolio-gambar"><img src="{{ $tutorial->gambar_url }}" width="800" height="600" loading="lazy" decoding="async" alt="{{ $tutorial->judul }}" class="gambar-konten"></div>
    <div class="kartu-portofolio-isi">
        <h2 class="judul-portofolio"><a href="{{ route('tutorial.show', $tutorial->slug) }}">{{ $tutorial->judul }}</a></h2>
        <p class="deskripsi-portofolio">{{ TeksHtml::potongDiAkhirKata(TeksHtml::polos($tutorial->deskripsi), 150) }}</p>
        <p class="jumlah-materi">{{ $tutorial->materi_tutorial_count }} materi</p>
    </div>
</article>
