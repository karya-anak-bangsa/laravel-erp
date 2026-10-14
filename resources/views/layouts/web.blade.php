<!DOCTYPE html>
{{-- Satu markup, dua tema: data-tema dirender server dari cookie 'tema' (TemaComposer) agar tidak berkedip. --}}
<html lang="id" data-tema="{{ $tema->value }}">
<head>
    @include('layouts.web.head')
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    @include('layouts.web.lompat-konten')

    @include('layouts.web.navbar')

    <main id="konten">
        @yield('konten')
    </main>

    @include('layouts.web.footer')
    @include('layouts.web.tombol-wa')

    @stack('akhir-body')
</body>
</html>
