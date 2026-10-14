<!DOCTYPE html>
{{-- Halaman error publik (HalamanErrorComposer), satu markup dua tema seperti layouts/web. Tanpa sesi & csrf_token()
     karena juga dirender untuk alamat yang tidak dikenal dan saat mode pemeliharaan. $identitas null = database tidak
     terbaca: bilah atas & kaki ringkas tanpa partial yang butuh data situs. --}}
@php($namaSitus = $identitas?->nama_perusahaan ?? config('perusahaan.nama'))
<html lang="id" data-tema="{{ $tema->value }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') — {{ $namaSitus }}</title>
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" href="{{ $identitas?->favicon_url ?? asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    @include('layouts.web.aset')
</head>
<body @class(['min-h-screen bg-background font-sans antialiased', 'flex flex-col' => ! $identitas])>
    @include('layouts.web.lompat-konten')

    @if ($identitas)
        @include('layouts.web.navbar')
    @else
        <header class="kepala-situs">
            <div class="bilah-navigasi">
                <a href="{{ route('beranda') }}" class="logo-situs merek-ringkas">{{ $namaSitus }}</a>
            </div>
        </header>
    @endif

    <main id="konten" @class(['halaman-galat', 'halaman-galat-ringkas' => ! $identitas])>
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="pola-kisi pola-pudar-tengah absolute inset-0"></div>
            <div class="absolute inset-0 mx-auto max-w-(--lebar-konten) mono:hidden">
                <div class="hero-blob top-[10%] -left-24 size-72 bg-(--warna-aksen)/15 sm:size-[26rem]"></div>
                <div class="hero-blob -right-24 bottom-[5%] size-80 bg-sky-400/20 sm:size-[30rem]"></div>
            </div>
        </div>

        <div class="tata-galat">
            <p class="kode-galat"><span class="sr-only">Kode error </span>{{ $kodeError }}</p>
            <h1 class="judul-galat">@yield('title')</h1>
            <p class="pesan-galat">@yield('message')</p>

            <div class="aksi-galat">
                @if ($kodeError === 503)
                    {{-- Selama pemeliharaan halaman lain pun menampilkan halaman ini, jadi cukup muat ulang. --}}
                    <a href="{{ url()->full() }}" class="btn btn-besar">Muat ulang</a>
                @else
                    <a href="{{ route('beranda') }}" class="btn btn-besar">Ke beranda</a>
                    {{-- Dimunculkan resources/js/web/kembali.js hanya bila tab ini punya halaman sebelumnya. --}}
                    <button type="button" class="btn btn-besar" data-variant="outline" data-kembali hidden>Kembali</button>
                @endif
            </div>
        </div>
    </main>

    @if ($identitas)
        @include('layouts.web.footer')
        @include('layouts.web.tombol-wa')
    @else
        <footer class="kaki-situs dark">
            <div class="garis-merek mono:hidden" aria-hidden="true"></div>
            <div class="wadah kaki-ringkas">
                <p>© {{ now()->year }} {{ $namaSitus }}. <span class="whitespace-nowrap">Hak cipta dilindungi.</span></p>
            </div>
        </footer>
    @endif
</body>
</html>
