{{-- Satu baris repeater email/telepon; $nama = email|telepon, $i = indeks (atau __i__ di <template>), $nilai = isian. --}}
@php
    [$tipe, $maks, $label, $contoh] = $nama === 'email'
        ? ['email', 150, 'Email', 'mis. info@karyaanakbangsa.co.id']
        : ['tel', 30, 'Telepon', 'mis. 0812 3456 7890'];
@endphp

<div class="repeater-baris" data-repeater-baris>
    <div class="repeater-isian">
        <input type="{{ $tipe }}" name="{{ $nama }}[{{ $i }}]" value="{{ $nilai }}" maxlength="{{ $maks }}" aria-label="{{ $label }}"
            placeholder="{{ $contoh }}" @class(['form-control', 'is-invalid' => $errors->has("$nama.$i")])>
        @error("$nama.$i")
            <div class="form-error">{{ $message }}</div>
        @enderror
    </div>
    <button type="button" class="btn btn-danger btn-icon" data-repeater-hapus title="Hapus {{ strtolower($label) }}"
        aria-label="Hapus {{ strtolower($label) }}">
        <x-admin.icon name="xmark" />
    </button>
</div>
