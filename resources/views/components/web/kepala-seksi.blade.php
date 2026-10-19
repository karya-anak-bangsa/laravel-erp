{{--
    Kepala seksi: label bergaris hiasan, judul h2, dan deskripsi (slot).
    Pakai: <x-web.kepala-seksi id="judul-layanan" label="Layanan" judul="…" rata="tengah">Deskripsi</x-web.kepala-seksi>
--}}
@props(['id', 'label', 'judul', 'rata' => null])

<div {{ $attributes->class('kepala-seksi') }} @if ($rata) data-rata="{{ $rata }}" @endif>
    <p class="label-seksi">{{ $label }}</p>
    <h2 id="{{ $id }}" class="judul-seksi">{{ $judul }}</h2>
    @if ($slot->isNotEmpty())
        <p class="deskripsi-seksi">{{ $slot }}</p>
    @endif
</div>
