<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>
{{-- Terapkan tema tersimpan sebelum halaman dirender agar mode gelap tidak berkedip. --}}
<script>(function(){try{var t=localStorage.getItem('theme');var d=window.matchMedia('(prefers-color-scheme: dark)').matches;document.documentElement.setAttribute('data-theme',t||(d?'dark':'light'));}catch(e){}})();</script>
@fonts
