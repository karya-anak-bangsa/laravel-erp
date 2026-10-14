{{-- Aset bersama layout publik & halaman error publik. Tidak menyentuh database. --}}
{{-- Token dua template aktif (TemaService). Isinya hanya dari enum & hex tervalidasi, jadi aman tanpa escape. --}}
<style id="token-template" @if (Vite::cspNonce()) nonce="{{ Vite::cspNonce() }}" @endif>{!! $tokenTemplate !!}</style>

@fonts($fontTema)
@vite(['resources/css/web.css', 'resources/js/web.js'])

{{-- Tanpa JavaScript: menu ponsel tetap terbaca; tombol tema disembunyikan karena tidak berfungsi. --}}
<noscript><style>.menu-ponsel[hidden]{display:block!important;position:static!important;box-shadow:none!important}.pilih-tema,.tema-ponsel{display:none!important}</style></noscript>
