{{--
    Isian formulir publik (Basecoat .field): label dengan bintang wajib di depan, nilai old(), dan galat validasi
    yang terhubung lewat aria-describedby. Pakai: <x-web.isian id="kontak-nama" nama="nama" label="Nama" :wajib="true" maks="100" />
    jenis: text | email | textarea.
--}}
@props(['id', 'nama', 'label', 'jenis' => 'text', 'wajib' => false, 'maks' => null, 'placeholder' => null, 'autocomplete' => null, 'baris' => 5])

@php($galat = $errors->first($nama))

<div role="group" {{ $attributes->class('field') }}>
    <label for="{{ $id }}"><span>@if ($wajib)<span class="tanda-wajib" aria-hidden="true">*</span>@endif{{ $label }}</span></label>
    @if ($jenis === 'textarea')
        <textarea id="{{ $id }}" name="{{ $nama }}" rows="{{ $baris }}" @if ($maks) maxlength="{{ $maks }}" @endif @required($wajib)
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            aria-invalid="{{ $galat ? 'true' : 'false' }}" @if ($galat) aria-describedby="{{ $id }}-galat" @endif>{{ old($nama) }}</textarea>
    @else
        <input type="{{ $jenis }}" id="{{ $id }}" name="{{ $nama }}" value="{{ old($nama) }}" @if ($maks) maxlength="{{ $maks }}" @endif @required($wajib)
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            aria-invalid="{{ $galat ? 'true' : 'false' }}" @if ($galat) aria-describedby="{{ $id }}-galat" @endif>
    @endif
    @if ($galat)
        <p id="{{ $id }}-galat" class="galat-isian">{{ $galat }}</p>
    @endif
</div>
