{{-- Dipakai create & edit; $kategoriTutorial = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Kategori Tutorial" subtitle="Mengelompokkan tutorial di website.">
        <x-admin.form-input name="nama_kategori" label="Nama Kategori" :value="$kategoriTutorial->nama_kategori" :required="true"
            maxlength="100" placeholder="mis. Laravel"
            :hint="match (true) {
                ! $kategoriTutorial->exists => 'Alamat halaman kategori (slug) dibuat otomatis dari nama dan tidak berubah lagi setelah kategori dipakai tutorial yang tampil di website.',
                $kategoriTutorial->slugTerkunci() => 'Alamat halaman kategori: /tutorial/kategori/'.$kategoriTutorial->slug.' — tetap meskipun nama diubah, agar tautan yang sudah tersebar tidak rusak.',
                default => 'Alamat halaman kategori: /tutorial/kategori/'.$kategoriTutorial->slug.' — ikut berubah bila nama diubah selama belum ada tutorial yang tampil di kategori ini.',
            }" />
    </x-admin.card>
</div>

<x-admin.form-aksi :batal="route('admin.kategori-tutorial.index')" />
