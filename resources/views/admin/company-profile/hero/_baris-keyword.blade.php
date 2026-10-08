{{-- Satu baris repeater keyword; $i = indeks (atau __i__ di <template>), $kata = nilai. --}}
<div class="repeater-baris" data-repeater-baris>
    <div class="repeater-isian">
        <input type="text" name="keyword[{{ $i }}]" value="{{ $kata }}" maxlength="50" aria-label="Keyword"
            placeholder="mis. Website" @class(['form-control', 'is-invalid' => $errors->has("keyword.$i")])>
        @error("keyword.$i")
            <div class="form-error">{{ $message }}</div>
        @enderror
    </div>
    <button type="button" class="btn btn-danger btn-icon" data-repeater-hapus title="Hapus keyword" aria-label="Hapus keyword">
        <x-admin.icon name="xmark" />
    </button>
</div>
