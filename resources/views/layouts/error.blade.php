<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Sengaja tanpa csrf_token(): halaman 500 bisa dirender saat sesi belum tersedia. --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ config('perusahaan.nama') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @fonts('inter')
    @vite('resources/scss/admin.scss')
</head>
<body>
    {{-- Halaman error panel admin & login (HalamanErrorComposer); view errors/* hanya berisi judul & pesan. --}}
    <div class="error-page">
        <div class="error-content">
            <div class="error-code">{{ $kodeError }}</div>
            <div class="error-title">@yield('title')</div>
            <div class="error-message">@yield('message')</div>

            <div class="error-actions">
                @switch($kodeError)
                    @case(419)
                        <a href="{{ url('/login') }}" class="btn btn-primary">Masuk kembali</a>
                        <a href="javascript:history.back()" class="btn btn-outline">← Kembali</a>
                        @break
                    @case(503)
                        <a href="javascript:location.reload()" class="btn btn-primary">Muat ulang</a>
                        @break
                    @default
                        <a href="{{ url('/admin') }}" class="btn btn-primary">
                            <x-admin.icon name="house" />
                            Ke dashboard
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline">
                            <x-admin.icon name="arrow-left" />
                            Kembali
                        </a>
                @endswitch
            </div>
        </div>
    </div>
</body>
</html>
