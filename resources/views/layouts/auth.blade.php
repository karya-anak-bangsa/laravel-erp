<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
    @vite('resources/scss/admin.scss')
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="brand-icon">T</div>
                <div class="brand-name">ERP TKAB</div>
            </div>

            @yield('content')
        </div>
    </div>
</body>
</html>
