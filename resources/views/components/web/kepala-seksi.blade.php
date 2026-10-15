{{--
    Kepala seksi: label (Full Color: merah bergaris; Monochrome: "01 / Label"), judul h2, dan deskripsi (slot).
    Nomor hanya untuk seksi beranda; seksi di halaman lain (mis. artikel lainnya) tanpa nomor.
    Pakai: <x-web.kepala-seksi id="judul-layanan" nomor="01" label="Layanan" judul="…" rata="tengah">Deskripsi</x-web.kepala-seksi>
--}}
@props(['id', 'nomor' => null, 'label', 'judul', 'rata' => null])

<div {{ $attributes->class('kepala-seksi') }} @if ($rata) data-rata="{{ $rata }}" @endif>
    <p class="label-seksi">@if ($nomor)<span class="label-nomor">{{ $nomor }}</span>@endif{{ $label }}</p>
    <h2 id="{{ $id }}" class="judul-seksi">{{ $judul }}</h2>
    @if ($slot->isNotEmpty())
        <p class="deskripsi-seksi">{{ $slot }}</p>
    @endif
</div>
