<section id="artikel" class="seksi" aria-labelledby="judul-artikel">
    <div class="wadah">
        {{-- "Lihat semua artikel": Full Color di bawah deskripsi (rata tengah), Monochrome di kanan kepala seksi. --}}
        <div class="kepala-artikel">
            <x-web.kepala-seksi id="judul-artikel" :nomor="$nomorSeksi('artikel')" label="Artikel" judul="Wawasan & kabar terbaru" rata="tengah">
                Tips, panduan, dan cerita seputar teknologi, pengembangan aplikasi, serta pelatihan IT.
            </x-web.kepala-seksi>
            <a href="{{ route('artikel.index') }}" class="btn btn-sedang" data-variant="outline">Lihat semua artikel</a>
        </div>

        {{-- Markup tiap item identik: Full Color menjadikan item pertama kartu utama, Monochrome menyusunnya sebagai daftar editorial. --}}
        <div class="daftar-artikel">
            @foreach ($artikel as $item)
                <x-web.kartu-artikel :artikel="$item" />
            @endforeach
        </div>
    </div>
</section>
