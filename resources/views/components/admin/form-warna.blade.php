@props(['name', 'label' => null, 'value' => null, 'required' => false, 'hint' => null])

@php
    $id = $attributes->get('id', $name);
    $nilai = old($name, $value);
    // Pemilih warna browser hanya menerima #rrggbb; isian lain (mis. salah ketik) tetap tampil di kotak hex.
    $nilaiPemilih = is_string($nilai) && preg_match('/^#[0-9a-f]{6}$/i', $nilai) === 1 ? strtolower($nilai) : '#000000';
@endphp

{{--
    Isian warna #rrggbb: pemilih warna browser + kotak hex yang saling mengisi (resources/js/admin/template.js).
    Yang terkirim kotak hex-nya, jadi tanpa JavaScript nilainya tetap bisa diketik.
--}}
<div class="form-group">
    @if ($label)
        <label class="form-label" for="{{ $id }}">@if ($required)<span class="required">*</span>@endif{{ $label }}</label>
    @endif

    <div class="isian-warna">
        <input type="color" value="{{ $nilaiPemilih }}" data-pilih-warna="{{ $id }}" aria-label="Pilih {{ Str::lower((string) $label) }}">
        <input type="text" id="{{ $id }}" name="{{ $name }}" @if (! is_null($nilai)) value="{{ $nilai }}" @endif
            maxlength="7" placeholder="#rrggbb" spellcheck="false" autocomplete="off" @required($required)
            {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    </div>

    @if ($hint)
        <div class="form-help">{{ $hint }}</div>
    @endif

    @error($name)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
