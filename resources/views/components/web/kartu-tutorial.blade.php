{{--
    Kartu tutorial (daftar & kategori tutorial): susunan dan kelas kartu artikel dipakai bersama (pilihan pemilik
    2026-10-20) — baris bergaris pemisah, teks kiri + gambar kanan, kategori sebagai pita mulai 640px (di bawahnya lencana
    di atas judul). Jumlah materi menggantikan tanggal terbit di bawah ringkasan. Judul h2 karena hanya dipakai di halaman
    daftar (di bawah h1).
    Pakai: <x-web.kartu-tutorial :tutorial="$item" /> — $item dari scope untukKartu().
--}}
@props(['tutorial'])

@use('App\Support\TeksHtml')

<article class="kartu-artikel">
    <div class="kartu-artikel-gambar"><img src="{{ $tutorial->gambar_url }}" width="800" height="600" loading="lazy" decoding="async" alt="{{ $tutorial->judul }}" class="gambar-konten"></div>
    <div class="kartu-artikel-isi">
        <div class="kartu-artikel-meta">
            <span class="lencana-artikel">{{ $tutorial->kategoriTutorial->nama_kategori }}</span>
            <span class="jumlah-materi">{{ $tutorial->materi_tutorial_count }} materi</span>
        </div>
        <h2 class="kartu-artikel-judul"><a href="{{ route('tutorial.show', $tutorial->slug) }}">{{ $tutorial->judul }}</a></h2>
        <p class="kartu-artikel-ringkasan">{{ TeksHtml::potongDiAkhirKata(TeksHtml::polos($tutorial->deskripsi), 150) }}</p>
    </div>
</article>
