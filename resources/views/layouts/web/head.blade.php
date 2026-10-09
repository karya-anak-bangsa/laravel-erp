<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('judul', $identitas->judul_website)</title>
<meta name="description" content="@yield('deskripsi', $identitas->meta_deskripsi)">
<meta name="theme-color" content="#ffffff">
<link rel="icon" href="{{ $identitas->favicon_url }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

{{-- Token dua template aktif (TemaService). Isinya hanya dari enum & hex tervalidasi, jadi aman tanpa escape. --}}
<style id="token-template" @if (Vite::cspNonce()) nonce="{{ Vite::cspNonce() }}" @endif>{!! $tokenTemplate !!}</style>

@fonts($fontTema)
@vite(['resources/css/web.css', 'resources/js/web.js'])

{{-- Tanpa JavaScript: menu ponsel tetap terbaca; tombol tema & tab portofolio disembunyikan karena tidak berfungsi. --}}
<noscript><style>.menu-ponsel[hidden]{display:block!important;position:static!important;box-shadow:none!important}.pilih-tema,.filter-portofolio>[role=tablist]{display:none!important}</style></noscript>
