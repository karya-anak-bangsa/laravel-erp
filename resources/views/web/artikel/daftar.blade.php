{{--
    Kartu artikel + paginasi di kolom utama halaman daftar & kategori artikel: daftar editorial bergaris pemisah,
    sama di kedua tema. Kategori tampil sebagai pita.
    Pakai: @include('web.artikel.daftar') — $artikel hasil ->paginate().
--}}
@if ($artikel->isEmpty())
    <p class="kosong-halaman">Belum ada artikel yang diterbitkan. Silakan kunjungi lagi nanti.</p>
@else
    <div class="grid-artikel">
        @foreach ($artikel as $item)
            <x-web.kartu-artikel :artikel="$item" tag-judul="h2" />
        @endforeach
    </div>

    <x-web.paginasi :paginator="$artikel" />
@endif
