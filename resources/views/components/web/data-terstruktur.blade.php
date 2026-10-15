{{--
    Data terstruktur JSON-LD (App\Support\DataTerstruktur) untuk <head>, dipasang lewat @push('kepala').
    JSON_HEX_TAG menulis < dan > sebagai </> sehingga isi data (mis. judul artikel) tidak bisa menutup tag script.
    Pakai: @push('kepala') <x-web.data-terstruktur :data="DataTerstruktur::beranda($identitas)" /> @endpush
--}}
@props(['data'])

<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_THROW_ON_ERROR) !!}</script>
