{{--
    Kotak Layanan kami di kolom samping halaman artikel, portofolio, & tutorial: tautan ke kartu layanannya di beranda,
    sama dengan kolom Layanan di footer. Selama seksi Layanan nonaktif (Manajemen Modul) kartunya tidak ada, jadi
    daftarnya tampil sebagai teks biasa.
    Pakai: @include('web.samping.layanan') — $layanan dari controller (footer memakai koleksi yang sama).
--}}
@if ($layanan->isNotEmpty())
    @php($tautanLayanan = $modul->tampil(App\Enums\CompanyProfile\JenisModul::Layanan))
    <{{ $tautanLayanan ? 'nav' : 'div' }} class="kotak-samping" aria-labelledby="judul-layanan-kami">
        <h2 id="judul-layanan-kami" class="judul-samping">Layanan kami</h2>
        <ul class="daftar-tautan-samping">
            @foreach ($layanan as $item)
                <li>
                    @if ($tautanLayanan)
                        <a href="{{ route('beranda') }}#layanan-{{ $item->id_layanan }}" class="tautan-samping">
                            <span>{{ $item->judul }}</span>
                            <x-web.ikon nama="panah-kanan" />
                        </a>
                    @else
                        <span class="tautan-samping"><span>{{ $item->judul }}</span></span>
                    @endif
                </li>
            @endforeach
        </ul>
    </{{ $tautanLayanan ? 'nav' : 'div' }}>
@endif
