<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Sengaja tanpa csrf_token(): halaman 500 bisa dirender saat sesi belum tersedia. --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>PT. Teknologi Karya Anak Bangsa</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <script>(function(){try{var t=localStorage.getItem('theme');var d=window.matchMedia('(prefers-color-scheme: dark)').matches;document.documentElement.setAttribute('data-theme',t||(d?'dark':'light'));}catch(e){}})();</script>
    @fonts
    @vite('resources/scss/admin.scss')
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <div class="error-code">@yield('code')</div>
            <div class="error-title">@yield('title')</div>
            <div class="error-message">@yield('message')</div>

            <div class="error-actions">
                @section('actions')
                    <a href="{{ url('/admin') }}" class="btn btn-primary">
                        <x-admin.icon name="house" />
                        Ke dashboard
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline">
                        <x-admin.icon name="arrow-left" />
                        Kembali
                    </a>
                @show
            </div>
        </div>
    </div>
</body>
</html>
