@props(['name', 'label' => null, 'accept' => null, 'required' => false, 'hint' => null, 'berkasSaatIni' => null])

{{-- Form pemakai wajib enctype="multipart/form-data". berkasSaatIni = URL file lama (halaman edit). --}}
@php
    $kunci = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $attributes->get('id', str_replace('.', '-', $kunci));
@endphp

<div class="form-group">
    @if ($label)
        <label class="form-label" for="{{ $id }}">@if ($required)<span class="required">*</span>@endif{{ $label }}</label>
    @endif

    <input type="file" id="{{ $id }}" name="{{ $name }}"
        @if ($accept) accept="{{ $accept }}" @endif
        @required($required)
        {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($kunci)]) }}>

    @if ($berkasSaatIni)
        <div class="form-help">
            <a href="{{ $berkasSaatIni }}" target="_blank" rel="noopener">Lihat file saat ini</a> — kosongkan bila tidak ingin mengganti.
        </div>
    @endif

    @if ($hint)
        <div class="form-help">{{ $hint }}</div>
    @endif

    @error($kunci)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
