@props(['name', 'label' => null, 'accept' => null, 'required' => false, 'hint' => null, 'berkasSaatIni' => null])

{{-- Form pemakai wajib enctype="multipart/form-data". berkasSaatIni = URL file lama (halaman edit). --}}
@php
    $kunci = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $attributes->get('id', str_replace('.', '-', $kunci));
    // Unggahan banyak berkas (name="galeri[]" multiple): galat tiap berkas tersimpan di galeri.0, galeri.1, dst.
    $galat = $errors->first($kunci) ?: $errors->first($kunci.'.*');
@endphp

<div class="form-group">
    @if ($label)
        <label class="form-label" for="{{ $id }}">@if ($required)<span class="required">*</span>@endif{{ $label }}</label>
    @endif

    <input type="file" id="{{ $id }}" name="{{ $name }}"
        @if ($accept) accept="{{ $accept }}" @endif
        @required($required)
        {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $galat !== '']) }}>

    @if ($berkasSaatIni)
        <div class="form-help">
            <a href="{{ $berkasSaatIni }}" target="_blank" rel="noopener">Lihat file saat ini</a> — kosongkan bila tidak ingin mengganti.
        </div>
    @endif

    @if ($hint)
        <div class="form-help">{{ $hint }}</div>
    @endif

    @if ($galat !== '')
        <div class="form-error">{{ $galat }}</div>
    @endif
</div>
