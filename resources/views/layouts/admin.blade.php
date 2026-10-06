<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
    @vite(['resources/scss/admin.scss', 'resources/js/admin.js'])
</head>
<body data-shell="admin">
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

    @include('layouts.partials.sidebar')
    @include('layouts.partials.topbar', ['breadcrumb' => $breadcrumb ?? []])

    <main id="main-content" tabindex="-1" class="main">
        <div class="page-wrapper">
            @yield('content')
        </div>

        @include('layouts.partials.footer')
    </main>

    @include('layouts.partials.flash')
</body>
</html>
