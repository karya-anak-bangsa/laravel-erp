@props(['name', 'label' => null, 'value' => null, 'required' => false, 'hint' => null, 'rows' => 4])

@php
    $kunci = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $attributes->get('id', str_replace('.', '-', $kunci));
@endphp

<div class="form-group">
    @if ($label)
        <label class="form-label" for="{{ $id }}">{{ $label }}@if ($required)<span class="required">*</span>@endif</label>
    @endif

    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
        @required($required)
        {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($kunci)]) }}>{{ old($kunci, $value) }}</textarea>

    @if ($hint)
        <div class="form-help">{{ $hint }}</div>
    @endif

    @error($kunci)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
