<section id="artikel" @class(['seksi', 'seksi-redup' => $redup]) aria-labelledby="judul-artikel">
    <div class="wadah">
        <x-web.kepala-seksi id="judul-artikel" label="Artikel" judul="Wawasan & kabar terbaru" rata="tengah">
            Tips, panduan, dan cerita seputar teknologi, pengembangan aplikasi, serta pelatihan IT.
        </x-web.kepala-seksi>

        {{-- Daftar bergaris pemisah, sama dengan halaman daftar artikel. --}}
        {{-- Tanpa tombol "Lihat semua artikel" (pilihan pemilik 2026-10-15): menu Artikel di navbar sudah mewakilinya. --}}
        <div class="daftar-artikel">
            @foreach ($artikel as $item)
                <x-web.kartu-artikel :artikel="$item" />
            @endforeach
        </div>
    </div>
</section>
