<!DOCTYPE html>
{{-- Satu markup, dua tema: data-tema dirender server dari cookie 'tema' (TemaComposer) agar tidak berkedip. --}}
<html lang="id" data-tema="{{ $tema->value }}">
<head>
    @include('layouts.web.head')
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-lg focus:bg-background focus:px-4 focus:py-2 focus:font-semibold focus:text-foreground focus:shadow-lg">Langsung ke konten</a>

    @include('layouts.web.navbar')

    <main id="konten">
        @yield('konten')
    </main>

    @include('layouts.web.footer')
    @include('layouts.web.tombol-wa')

    @stack('akhir-body')
</body>
</html>
