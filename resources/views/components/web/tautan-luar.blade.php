{{--
    Tautan ke situs lain (WhatsApp, peta): tab baru + rel noopener, dan keterangan untuk pembaca layar.
    Pakai: <x-web.tautan-luar :href="$url" class="btn">Teks</x-web.tautan-luar>
--}}
@props(['href'])

<a href="{{ $href }}" target="_blank" rel="noopener" {{ $attributes }}>{{ $slot }}<span class="sr-only"> (membuka tab baru)</span></a>
