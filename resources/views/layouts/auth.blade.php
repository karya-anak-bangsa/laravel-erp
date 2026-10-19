<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
    @vite(['resources/scss/admin.scss', 'resources/js/auth.js'])
</head>
<body>
    <div class="auth-page">
        <div class="auth-split">
            {{-- Panel identitas; disembunyikan di layar ≤768px sehingga kartu hanya berisi form. --}}
            <div class="auth-aside">
                <div class="auth-aside-label">PT. Teknologi Karya Anak Bangsa</div>
                <div class="auth-aside-title">Panel Admin ERP</div>
                <div class="auth-aside-text">Kelola data, layanan, dan operasional perusahaan dalam satu sistem terpadu.</div>
                <div class="auth-aside-footer">&copy; {{ now()->year }} PT. Teknologi Karya Anak Bangsa</div>
            </div>

            <div class="auth-main">
                <div class="auth-logo">
                    <img src="{{ asset('img/logo-tkab.webp') }}" alt="PT. Teknologi Karya Anak Bangsa" width="128" height="48">
                </div>

                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
