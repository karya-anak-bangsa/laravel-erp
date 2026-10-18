{{--
    Kotak Layanan kami di kolom samping halaman artikel & portofolio: tautan ke kartu layanannya di beranda, sama dengan
    kolom Layanan di footer.
    Pakai: @include('web.samping.layanan') — $layanan dari controller (footer memakai koleksi yang sama).
--}}
@if ($layanan->isNotEmpty())
    <nav class="kotak-samping" aria-labelledby="judul-layanan-kami">
        <h2 id="judul-layanan-kami" class="judul-samping">Layanan kami</h2>
        <ul class="daftar-tautan-samping">
            @foreach ($layanan as $item)
                <li>
                    <a href="{{ route('beranda') }}#layanan-{{ $item->id_layanan }}" class="tautan-samping">
                        <span>{{ $item->judul }}</span>
                        <x-web.ikon nama="panah-kanan" />
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
