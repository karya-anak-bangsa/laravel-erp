<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
    @vite(['resources/scss/admin.scss', 'resources/js/auth.js'])
</head>
<body>
    <div class="auth-page">
        <div class="auth-wrap">
            <div class="auth-card">
                <div class="auth-logo">
                    <img src="{{ asset('img/logo-tkab.webp') }}" alt="PT. Teknologi Karya Anak Bangsa" width="128" height="48">
                </div>

                @yield('content')
            </div>

            <div class="auth-footer">&copy; {{ now()->year }} PT. Teknologi Karya Anak Bangsa</div>
        </div>
    </div>
</body>
</html>
