{{-- Dipakai create & edit; $kategoriArtikel = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Kategori Artikel" subtitle="Mengelompokkan artikel di website.">
        <x-admin.form-input name="nama_kategori" label="Nama Kategori" :value="$kategoriArtikel->nama_kategori" :required="true"
            maxlength="100" placeholder="mis. Tips & Tutorial"
            :hint="match (true) {
                ! $kategoriArtikel->exists => 'Alamat halaman kategori (slug) dibuat otomatis dari nama dan tidak berubah lagi setelah kategori dipakai artikel terbit.',
                $kategoriArtikel->slugTerkunci() => 'Alamat halaman kategori: /artikel/kategori/'.$kategoriArtikel->slug.' — tetap meskipun nama diubah, agar tautan yang sudah tersebar tidak rusak.',
                default => 'Alamat halaman kategori: /artikel/kategori/'.$kategoriArtikel->slug.' — ikut berubah bila nama diubah selama belum ada artikel terbit di kategori ini.',
            }" />
    </x-admin.card>
</div>

<x-admin.form-aksi :batal="route('admin.kategori-artikel.index')" />
