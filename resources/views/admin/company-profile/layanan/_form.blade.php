@use('App\Models\CompanyProfile\Layanan')

{{-- Dipakai create & edit; $layanan = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Konten Layanan" subtitle="Tampil sebagai kartu layanan di beranda website.">
        <x-admin.form-input name="judul" label="Judul" :value="$layanan->judul" :required="true" maxlength="150"
            placeholder="mis. Pembuatan Website" />
        <x-admin.form-textarea name="deskripsi" label="Deskripsi" :value="$layanan->deskripsi" :required="true"
            rows="3" maxlength="1000" hint="Ringkasan singkat yang tampil di kartu layanan." />
        <x-admin.form-textarea name="keterangan" label="Keterangan" :value="$layanan->keterangan" rows="6"
            maxlength="5000" hint="Penjelasan lengkap layanan, mis. cakupan pekerjaan atau keunggulan. Opsional." />
    </x-admin.card>

    <x-admin.card title="Gambar & Urutan">
        @if ($layanan->exists)
            <div class="pratinjau-gambar pratinjau-gambar-lebar">
                <img src="{{ $layanan->gambar_url }}" alt="Gambar layanan saat ini">
            </div>
        @endif
        <x-admin.form-file name="gambar" label="Gambar" accept=".jpg,.jpeg,.png,.webp" :required="! $layanan->exists"
            :hint="($layanan->exists ? 'Kosongkan bila tidak ingin mengganti. ' : '').'JPG, PNG, atau WEBP, maks. 2 MB. Disarankan rasio 4:3, mis. 800×600 px.'" />

        <x-admin.form-input name="urutan_ke" type="number" label="Urutan ke" :value="$layanan->urutan_ke" :required="true"
            min="0" :max="Layanan::URUTAN_MAKS" inputmode="numeric"
            hint="Angka kecil tampil lebih dulu. Angka yang sama diurutkan berdasarkan judul." />
    </x-admin.card>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.layanan.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
