{{--
    Kartu tutorial + paginasi di kolom utama halaman daftar & kategori tutorial: daftar bergaris pemisah seperti daftar
    artikel, sama di kedua tema.
    Pakai: @include('web.tutorial.daftar') — $tutorial hasil ->paginate().
--}}
@if ($tutorial->isEmpty())
    <p class="kosong-halaman">Belum ada tutorial. Silakan kunjungi lagi nanti.</p>
@else
    <div class="grid-artikel">
        @foreach ($tutorial as $item)
            <x-web.kartu-tutorial :tutorial="$item" />
        @endforeach
    </div>

    <x-web.paginasi :paginator="$tutorial" />
@endif
