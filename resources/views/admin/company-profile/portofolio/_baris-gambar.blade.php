{{-- Satu baris repeater gambar tambahan; $i = indeks (atau __i__ di <template>), $gambar = GambarPortofolio tersimpan atau
     null untuk baris unggahan baru. --}}
<div class="repeater-baris baris-gambar" data-repeater-baris>
    <div class="pratinjau-gambar pratinjau-gambar-baris">
        @if ($gambar)
            <img src="{{ $gambar->gambar_url }}" alt="Gambar tambahan tersimpan">
        @else
            {{-- Diisi berkas yang dipilih (admin.js). --}}
            <img alt="Pratinjau gambar tambahan" data-pratinjau-berkas hidden>
        @endif
    </div>
    <div class="repeater-isian">
        @if ($gambar)
            <input type="hidden" name="galeri_lama[{{ $i }}]" value="{{ $gambar->id_gambar_portofolio }}">
            <div class="baris-gambar-keterangan">Tersimpan</div>
        @else
            <input type="file" name="galeri[{{ $i }}]" accept=".jpg,.jpeg,.png,.webp" aria-label="Gambar tambahan"
                class="form-control" data-pratinjau-sumber>
        @endif
    </div>
    <button type="button" class="btn btn-danger btn-icon" data-repeater-hapus title="Buang gambar" aria-label="Buang gambar">
        <x-admin.icon name="xmark" />
    </button>
</div>
