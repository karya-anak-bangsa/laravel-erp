{{--
    Kolom samping halaman tutorial, susunannya sama dengan kolom samping portofolio: Daftar materi (hanya di halaman
    materi; materi yang sedang dibuka ditandai) → Konsultasi → Layanan kami.
    Pakai: @include('web.tutorial.samping', ['materiAktif' => $materiTutorial]) — $layanan dari controller; di halaman
    materi juga $tutorial dan $daftarMateri.
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
</aside>
