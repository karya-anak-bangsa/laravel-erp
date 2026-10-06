@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null])

@php
    // Nama array (cta[0][label]) dikonversi ke notasi titik untuk old() & error.
    $kunci = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $attributes->get('id', str_replace('.', '-', $kunci));
    $nilai = $type === 'password' ? null : old($kunci, $value);
@endphp

<div class="form-group">
    @if ($label)
        <label class="form-label" for="{{ $id }}">{{ $label }}@if ($required)<span class="required">*</span>@endif</label>
    @endif

    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
        @if (! is_null($nilai)) value="{{ $nilai }}" @endif
        @required($required)
        {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($kunci)]) }}>

    @if ($hint)
        <div class="form-help">{{ $hint }}</div>
    @endif

    @error($kunci)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
