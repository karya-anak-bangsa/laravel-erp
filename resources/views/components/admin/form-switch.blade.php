@props(['name', 'label' => null, 'text' => null, 'checked' => false, 'hint' => null])

@php
    $kunci = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $id = $attributes->get('id', str_replace('.', '-', $kunci));
    $aktif = filter_var(old($kunci, $checked), FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="form-group">
    @if ($label)
        <div class="form-label">{{ $label }}</div>
    @endif

    {{-- Hidden input memastikan nilai 0 tetap terkirim saat switch dimatikan. --}}
    <input type="hidden" name="{{ $name }}" value="0">
    <label class="switch">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($aktif) {{ $attributes->except('id') }}>
        <span class="track"></span>
        @if ($text)
            <span class="switch-label">{{ $text }}</span>
        @endif
    </label>

    @if ($hint)
        <div class="form-help">{{ $hint }}</div>
    @endif

    @error($kunci)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
