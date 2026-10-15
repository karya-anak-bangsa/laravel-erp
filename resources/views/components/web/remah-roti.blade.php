{{--
    Remah roti (breadcrumb) halaman publik selain beranda, bergaya label seksi. $jejak = [label => url]; url null =
    halaman ini. Halaman detail cukup sampai induknya karena judulnya tepat di bawah.
    Pakai: <x-web.remah-roti :jejak="['Beranda' => route('beranda'), 'Artikel' => null]" />
--}}
@props(['jejak'])

<nav {{ $attributes->class('remah-roti') }} aria-label="Lokasi halaman">
    <ol>
        @foreach ($jejak as $label => $url)
            <li>
                @if ($url)
                    <a href="{{ $url }}">{{ $label }}</a>
                @else
                    <span aria-current="page">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
