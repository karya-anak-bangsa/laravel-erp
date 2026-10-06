<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Sengaja tanpa csrf_token(): halaman 500 bisa dirender saat sesi belum tersedia. --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('code') · @yield('title') · {{ config('app.name') }}</title>
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
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M8 2L2 7v7h12V7L8 2z"/><path d="M6 14V9h4v5"/></svg>
                        Ke dashboard
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline">← Kembali</a>
                @show
            </div>
        </div>
    </div>
</body>
</html>
