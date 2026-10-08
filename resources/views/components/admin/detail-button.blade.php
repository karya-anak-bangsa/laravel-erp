@props([
    'title' => 'Detail data',
    'label' => 'Lihat',
    'ikonSaja' => false,
])

@php
    $idTemplat = 'detail-'.Str::random(12);
@endphp

{{--
    Tombol Lihat: membuka rincian data (isi slot) dalam modal SweetAlert2, dipasang oleh
    resources/js/admin.js lewat atribut data-detail. Isi slot dirender Blade (sudah di-escape)
    ke <template> sehingga modal terbuka tanpa request tambahan.
    :ikon-saja="true" = tombol ikon persegi 32px tanpa teks, setinggi tombol Tambah
    (label tetap di tooltip & aria-label).
--}}
<button type="button" @class(['btn', 'btn-sm' => ! $ikonSaja, 'btn-info', 'btn-ikon' => $ikonSaja]) title="{{ $label }}"
    @if ($ikonSaja) aria-label="{{ $label }}" @endif
    data-detail="{{ $idTemplat }}" data-detail-title="{{ $title }}" {{ $attributes }}>
    <x-admin.icon name="eye" />
    @unless ($ikonSaja)
        {{ $label }}
    @endunless
</button>
<template id="{{ $idTemplat }}">
    <div class="detail-data">{{ $slot }}</div>
</template>
