@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'required' => false,
    'hint' => null,
    'placeholder' => '— Pilih —',
])

@php
    // options: [nilai => label]. Nilai dibandingkan sebagai string agar 1 == '1' dan enum->value cocok.
    $kunci = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $attributes->get('id', str_replace('.', '-', $kunci));
    $terpilih = old($kunci, $value instanceof BackedEnum ? $value->value : $value);
@endphp

<div class="form-group">
    @if ($label)
        <label class="form-label" for="{{ $id }}">@if ($required)<span class="required">*</span>@endif{{ $label }}</label>
    @endif

    <select id="{{ $id }}" name="{{ $name }}"
        @required($required)
        {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($kunci)]) }}>
        @if ($placeholder !== false)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $nilai => $teks)
            {{-- Nilai berupa array = grup: ['Label grup' => [nilai => label]] → <optgroup>. --}}
            @if (is_array($teks))
                <optgroup label="{{ $nilai }}">
                    @foreach ($teks as $nilaiAnak => $teksAnak)
                        <option value="{{ $nilaiAnak }}" @selected((string) $terpilih === (string) $nilaiAnak)>{{ $teksAnak }}</option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $nilai }}" @selected((string) $terpilih === (string) $nilai)>{{ $teks }}</option>
            @endif
        @endforeach
    </select>

    @if ($hint)
        <div class="form-help">{{ $hint }}</div>
    @endif

    @error($kunci)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
