<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
    @vite(['resources/scss/admin.scss', 'resources/js/auth.js'])
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="brand-icon"><i class="fa-solid fa-landmark" aria-hidden="true"></i></div>
                <div class="brand-name">PT. Teknologi Karya Anak Bangsa</div>
            </div>

            @yield('content')
        </div>
    </div>
</body>
</html>
