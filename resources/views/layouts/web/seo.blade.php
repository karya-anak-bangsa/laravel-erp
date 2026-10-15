{{-- Tag mesin pencari & pratinjau tautan (SeoComposer), lalu tag khusus halaman dari @push('kepala'), mis. JSON-LD. --}}
<title>{{ $seo['judul'] }}</title>
@if (filled($seo['deskripsi']))
    <meta name="description" content="{{ $seo['deskripsi'] }}">
@endif
{{-- noindex selama SEO_INDEKS=false (tahap uji, config/seo.php) dan di pratinjau template. --}}
<meta name="robots" content="{{ $seo['indeks'] ? 'max-image-preview:large' : 'noindex' }}">
<link rel="canonical" href="{{ $seo['kanonis'] }}">

<meta property="og:site_name" content="{{ $identitas->nama_perusahaan }}">
<meta property="og:locale" content="id_ID">
<meta property="og:type" content="{{ $seo['jenis'] }}">
<meta property="og:title" content="{{ $seo['judul'] }}">
@if (filled($seo['deskripsi']))
    <meta property="og:description" content="{{ $seo['deskripsi'] }}">
@endif
<meta property="og:url" content="{{ $seo['kanonis'] }}">
@if ($seo['gambar'])
    <meta property="og:image" content="{{ $seo['gambar'] }}">
    @if ($seo['ukuranGambar'])
        <meta property="og:image:width" content="{{ $seo['ukuranGambar'][0] }}">
        <meta property="og:image:height" content="{{ $seo['ukuranGambar'][1] }}">
    @endif
    @if (filled($seo['altGambar']))
        <meta property="og:image:alt" content="{{ $seo['altGambar'] }}">
    @endif
@endif
{{-- X/Twitter memakai og:* untuk judul, deskripsi, dan gambar; yang wajib hanya jenis kartunya. --}}
<meta name="twitter:card" content="{{ $seo['gambar'] ? 'summary_large_image' : 'summary' }}">
@stack('kepala')
