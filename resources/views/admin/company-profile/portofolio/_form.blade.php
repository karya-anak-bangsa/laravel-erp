{{-- Dipakai create & edit; $portofolio = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Konten Portofolio" subtitle="Tampil di halaman portofolio website beserta halaman detailnya.">
        <x-admin.form-input name="judul" label="Judul" :value="$portofolio->judul" :required="true" maxlength="200"
            placeholder="mis. Website Profil Sekolah Harapan Bangsa"
            :hint="$portofolio->exists
                ? 'Alamat halaman detail: /portofolio/'.$portofolio->slug.' — ikut berubah bila judul diubah.'
                : 'Alamat halaman detail (slug) dibuat otomatis dari judul.'" />
        <x-admin.form-input name="kategori" label="Kategori" :value="$portofolio->kategori" :required="true" maxlength="50"
            list="saran-kategori" autocomplete="off" placeholder="mis. Website"
            hint="Pilih dari saran agar penulisan kategori seragam, atau ketik kategori baru." />
        <datalist id="saran-kategori">
            @foreach ($daftarKategori as $kategori)
                <option value="{{ $kategori }}"></option>
            @endforeach
        </datalist>
        <x-admin.form-editor name="deskripsi" label="Deskripsi" :value="$portofolio->deskripsi" :required="true"
            :maks="5000" hint="Gambaran proyek, mis. klien, kebutuhan, fitur, dan teknologi yang dipakai." />
    </x-admin.card>

    <x-admin.card title="Gambar">
        @if ($portofolio->exists)
            <div class="pratinjau-gambar pratinjau-gambar-lebar">
                <img src="{{ $portofolio->gambar_url }}" alt="Gambar portofolio saat ini">
            </div>
        @endif
        <x-admin.form-file name="gambar" label="Gambar" accept=".jpg,.jpeg,.png,.webp" :required="! $portofolio->exists"
            :hint="($portofolio->exists ? 'Kosongkan bila tidak ingin mengganti. ' : '').'JPG, PNG, atau WEBP, maks. 2 MB. Disarankan rasio 4:3, mis. 800×600 px.'" />
    </x-admin.card>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.portofolio.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
