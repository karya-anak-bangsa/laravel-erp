<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('layouts.web.seo')
<meta name="theme-color" content="#ffffff">
<link rel="icon" href="{{ $identitas->favicon_url }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

@include('layouts.web.aset')
