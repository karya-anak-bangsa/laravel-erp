@use('App\Enums\CompanyProfile\GayaCta')

{{-- Satu baris repeater tombol CTA; $i = indeks (atau __i__ di <template>), $tombol = ['label','url','gaya']. --}}
<div class="repeater-baris" data-repeater-baris>
    <div class="repeater-isian repeater-isian-cta">
        <div>
            <input type="text" name="cta[{{ $i }}][label]" value="{{ $tombol['label'] ?? '' }}" maxlength="30"
                aria-label="Label tombol" placeholder="Label, mis. Hubungi Kami"
                @class(['form-control', 'is-invalid' => $errors->has("cta.$i.label")])>
            @error("cta.$i.label")
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <input type="text" name="cta[{{ $i }}][url]" value="{{ $tombol['url'] ?? '' }}" maxlength="255"
                aria-label="URL tombol" placeholder="URL, mis. #kontak atau /portofolio"
                @class(['form-control', 'is-invalid' => $errors->has("cta.$i.url")])>
            @error("cta.$i.url")
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <select name="cta[{{ $i }}][gaya]" aria-label="Gaya tombol"
                @class(['form-control', 'is-invalid' => $errors->has("cta.$i.gaya")])>
                @foreach (GayaCta::opsi() as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(($tombol['gaya'] ?? GayaCta::Primary->value) === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            @error("cta.$i.gaya")
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <button type="button" class="btn btn-danger btn-icon" data-repeater-hapus title="Hapus tombol" aria-label="Hapus tombol">
        <x-admin.icon name="xmark" />
    </button>
</div>
