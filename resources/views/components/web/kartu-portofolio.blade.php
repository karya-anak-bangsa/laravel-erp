{{--
    Kartu portofolio (beranda & daftar portofolio). Markup identik di kedua tema; corak warna per kategori (latar gambar)
    hanya berwarna di Full Color. Kategori tampil sebagai pita di tepi kanan kartu, sama dengan pita kartu artikel, di
    kedua tema dan semua lebar (pilihan pemilik 2026-10-18). Judul h3 di bawah kepala seksi beranda; h2 di halaman daftar
    (di bawah h1).
    Pakai: <x-web.kartu-portofolio :portofolio="$item" tag-judul="h2" />
--}}
@props(['portofolio', 'tagJudul' => 'h3'])

@use('App\Support\AksenWarna')
@use('App\Support\TeksHtml')

<article class="kartu-portofolio {{ AksenWarna::untuk($portofolio->kategoriPortofolio->nama_kategori) }}">
    <span class="pita-kategori">{{ $portofolio->kategoriPortofolio->nama_kategori }}</span>
    <div class="kartu-portofolio-gambar"><img src="{{ $portofolio->gambar_url }}" width="800" height="600" loading="lazy" decoding="async" alt="{{ $portofolio->judul }}" class="gambar-konten"></div>
    <div class="kartu-portofolio-isi">
        {{-- Hanya judul yang bertautan ke detail, sama dengan kartu artikel (pilihan pemilik 2026-10-17). --}}
        <{{ $tagJudul }} class="judul-portofolio"><a href="{{ route('portofolio.show', $portofolio->slug) }}">{{ $portofolio->judul }}</a></{{ $tagJudul }}>
        {{-- Maksimal 150 karakter termasuk spasi, dipotong di akhir kata, sama dengan kartu artikel (pilihan pemilik 2026-10-18). --}}
        <p class="deskripsi-portofolio">{{ TeksHtml::potongDiAkhirKata(TeksHtml::polos($portofolio->deskripsi), 150) }}</p>
    </div>
</article>
