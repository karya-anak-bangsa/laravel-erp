{{-- Tanpa tab filter kategori (keputusan pemilik 2026-10-11): portofolio nyata masih didominasi website & mobile apps.
     Tanpa tombol "Lihat semua portofolio" (pilihan pemilik 2026-10-18): menu Portofolio di navbar sudah mewakilinya. --}}
<section id="portofolio" @class(['seksi', 'seksi-redup' => $redup]) aria-labelledby="judul-portofolio">
    <div class="wadah">
        <x-web.kepala-seksi id="judul-portofolio" label="Portofolio" judul="Hasil karya kami" rata="tengah">
            Beberapa proyek dan program yang telah kami kerjakan bersama klien dan mitra.
        </x-web.kepala-seksi>

        <ul class="daftar-portofolio mt-10 sm:mt-14" data-jumlah="{{ $portofolio->count() }}">
            @foreach ($portofolio as $item)
                <li><x-web.kartu-portofolio :portofolio="$item" /></li>
            @endforeach
        </ul>
        @if ($portofolio->count() > 1)
            <p class="petunjuk-geser" aria-hidden="true">
                Geser untuk melihat lainnya
                <x-web.ikon nama="panah-kanan" class="size-3.5" />
            </p>
        @endif
    </div>
</section>
