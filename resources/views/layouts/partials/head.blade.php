<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- Panel admin & login bukan untuk mesin pencari (robots.txt juga menolak /admin dan /login). --}}
<meta name="robots" content="noindex, nofollow">
<title>PT. Teknologi Karya Anak Bangsa</title>
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
@fonts('inter')
