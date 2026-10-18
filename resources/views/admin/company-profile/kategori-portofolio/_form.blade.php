{{-- Dipakai create & edit; $kategoriPortofolio = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Kategori Portofolio" subtitle="Mengelompokkan portofolio di website.">
        <x-admin.form-input name="nama_kategori" label="Nama Kategori" :value="$kategoriPortofolio->nama_kategori" :required="true"
            maxlength="100" placeholder="mis. Website"
            hint="Tampil sebagai pita kategori di kartu dan halaman detail portofolio." />
    </x-admin.card>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.kategori-portofolio.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
