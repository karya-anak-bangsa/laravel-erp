@php
    // Seksi beranda lewat alamat beranda + anchor agar tetap berfungsi dari halaman publik lain; Portofolio, Tutorial,
    // dan Artikel punya halaman sendiri.
    $menuUtama = [
        'Beranda' => route('beranda').'#beranda',
        'Layanan' => route('beranda').'#layanan',
        'Portofolio' => route('portofolio.index'),
        'Tutorial' => route('tutorial.index'),
        'Artikel' => route('artikel.index'),
        'FAQ' => route('beranda').'#faq',
    ];
    // Menu halaman tersendiri ditandai: "page" di halaman itu sendiri, "true" di halaman turunannya (detail portofolio,
    // halaman, materi & kategori tutorial, detail & kategori artikel).
    $menuAktif = fn (string $label): ?string => match (true) {
        $label === 'Portofolio' && request()->routeIs('portofolio.index') => 'page',
        $label === 'Portofolio' && request()->routeIs('portofolio.show') => 'true',
        $label === 'Tutorial' && request()->routeIs('tutorial.index') => 'page',
        $label === 'Tutorial' && request()->routeIs('tutorial.show', 'materi-tutorial.show', 'kategori-tutorial.show') => 'true',
        $label === 'Artikel' && request()->routeIs('artikel.index') => 'page',
        $label === 'Artikel' && request()->routeIs('artikel.show', 'kategori-artikel.show') => 'true',
        default => null,
    };
@endphp

<header class="kepala-situs">
    <div class="bilah-navigasi">
        {{-- Logo identitas wajib berlatar transparan: Monochrome menghitamkannya lewat filter. --}}
        <a href="{{ route('beranda') }}#beranda" class="logo-situs" aria-label="{{ $identitas->nama_perusahaan }} — ke beranda">
            <img src="{{ $identitas->logo_url }}" alt="" width="107" height="40" class="logo-gambar">
            <span class="pemisah-navbar" aria-hidden="true"></span>
            <span class="nama-navbar">{{ $identitas->nama_perusahaan }}</span>
        </a>

        <nav aria-label="Menu utama" class="menu-utama">
            <ul>
                @foreach ($menuUtama as $label => $url)
                    <li><a href="{{ $url }}" class="tautan-menu" @if ($menuAktif($label)) aria-current="{{ $menuAktif($label) }}" @endif>{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        <div class="aksi-navbar">
            {{-- Pilihan tema disimpan di cookie 'tema' oleh resources/js/web/tema.js. --}}
            <div class="pilih-tema" role="group" aria-label="Tema tampilan">
                @foreach (App\Enums\CompanyProfile\JenisTemplate::cases() as $jenis)
                    <button type="button" class="opsi-tema" data-pilih-tema="{{ $jenis->value }}" aria-pressed="{{ $tema === $jenis ? 'true' : 'false' }}" title="{{ $jenis->label() }}">
                        <span @class(['contoh-warna', 'contoh-warna-penuh' => $jenis === App\Enums\CompanyProfile\JenisTemplate::FullColor, 'contoh-warna-mono' => $jenis === App\Enums\CompanyProfile\JenisTemplate::Monochrome]) aria-hidden="true"></span>
                        <span class="sr-only">{{ $jenis->label() }}</span>
                    </button>
                @endforeach
            </div>

            <button type="button" id="tombol-menu" class="btn tombol-menu" data-variant="outline" data-size="icon" aria-expanded="false" aria-controls="menu-ponsel" aria-label="Buka menu">
                <x-web.ikon nama="menu" class="ikon-buka size-5" />
                <x-web.ikon nama="tutup" class="ikon-tutup hidden size-5" />
            </button>
        </div>
    </div>

    {{-- Menu ponsel melayang di bawah header agar tinggi header (dan tujuan gulir anchor) tetap. --}}
    <div id="menu-ponsel" class="menu-ponsel" hidden>
        <nav aria-label="Menu utama (ponsel)">
            <ul>
                @foreach ($menuUtama as $label => $url)
                    <li><a href="{{ $url }}" @if ($menuAktif($label)) aria-current="{{ $menuAktif($label) }}" @endif>{{ $label }}</a></li>
                @endforeach
            </ul>

            {{-- Versi berlabel dari tombol tema di navbar; keduanya dikendalikan resources/js/web/tema.js. --}}
            <div class="tema-ponsel">
                <span class="tema-ponsel-label" id="label-tema-ponsel">Tema</span>
                <div class="pilih-tema" role="group" aria-labelledby="label-tema-ponsel">
                    @foreach (App\Enums\CompanyProfile\JenisTemplate::cases() as $jenis)
                        <button type="button" class="opsi-tema" data-pilih-tema="{{ $jenis->value }}" aria-pressed="{{ $tema === $jenis ? 'true' : 'false' }}">
                            <span @class(['contoh-warna', 'contoh-warna-penuh' => $jenis === App\Enums\CompanyProfile\JenisTemplate::FullColor, 'contoh-warna-mono' => $jenis === App\Enums\CompanyProfile\JenisTemplate::Monochrome]) aria-hidden="true"></span>
                            {{ $jenis->label() }}
                        </button>
                    @endforeach
                </div>
            </div>
        </nav>
    </div>
</header>

{{-- Di luar <header>: backdrop-filter header menjadikannya acuan posisi fixed, jadi latar tidak bisa menutup halaman dari dalam. --}}
<div id="latar-menu" class="latar-menu" hidden></div>
