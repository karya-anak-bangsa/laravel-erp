<section id="artikel" class="seksi" aria-labelledby="judul-artikel">
    <div class="wadah">
        <x-web.kepala-seksi id="judul-artikel" :nomor="$nomorSeksi('artikel')" label="Artikel" judul="Wawasan & kabar terbaru" rata="tengah">
            Tips, panduan, dan cerita seputar teknologi, pengembangan aplikasi, serta pelatihan IT.
        </x-web.kepala-seksi>

        {{-- Markup tiap item identik: Full Color menjadikan item pertama kartu utama, Monochrome menyusunnya sebagai daftar editorial. --}}
        <div class="daftar-artikel">
            @foreach ($artikel as $item)
                <x-web.kartu-artikel :artikel="$item" />
            @endforeach
        </div>

        {{-- Di bawah daftar (pilihan pemilik 2026-10-15): Full Color rata tengah, Monochrome rata kiri, seperti kepala seksinya. --}}
        <div class="aksi-artikel">
            <a href="{{ route('artikel.index') }}" class="btn btn-sedang tombol-semua-artikel">Lihat semua artikel</a>
        </div>
    </div>
</section>
