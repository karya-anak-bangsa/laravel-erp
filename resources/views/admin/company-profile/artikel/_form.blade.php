@use('App\Enums\CompanyProfile\StatusPublikasi')
@use('App\Http\Requests\Admin\CompanyProfile\StoreArtikelRequest')

{{-- Dipakai create & edit; $artikel = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Konten Artikel" subtitle="Tampil di halaman artikel website beserta halaman detailnya.">
        <x-admin.form-input name="judul" label="Judul" :value="$artikel->judul" :required="true" maxlength="200"
            placeholder="mis. 5 Alasan Bisnis Kecil Perlu Website Sendiri"
            :hint="$artikel->exists
                ? 'Alamat halaman detail: /artikel/'.$artikel->slug.' — ikut berubah bila judul diubah.'
                : 'Alamat halaman detail (slug) dibuat otomatis dari judul.'" />
        <x-admin.form-editor name="deskripsi" label="Isi Artikel" :value="$artikel->deskripsi" :required="true"
            :maks="StoreArtikelRequest::PANJANG_ISI_MAKS" :judul-bagian="true" :baris="15"
            hint="Gunakan sub-judul (H2/H3) untuk membagi artikel menjadi beberapa bagian." />
    </x-admin.card>

    <x-admin.card title="Gambar & Publikasi">
        @if ($artikel->exists)
            <div class="pratinjau-gambar pratinjau-gambar-lebar">
                <img src="{{ $artikel->gambar_url }}" alt="Gambar artikel saat ini">
            </div>
        @endif
        <x-admin.form-file name="gambar" label="Gambar" accept=".jpg,.jpeg,.png,.webp" :required="! $artikel->exists"
            :hint="($artikel->exists ? 'Kosongkan bila tidak ingin mengganti. ' : '').'JPG, PNG, atau WEBP, maks. 2 MB. Disarankan rasio ±2:1, mis. 1200×630 px (ukuran pratinjau media sosial).'" />

        <x-admin.form-select name="id_kategori_artikel" label="Kategori" :options="$opsiKategori"
            :value="$artikel->id_kategori_artikel" :required="true"
            :hint="$opsiKategori === [] ? 'Belum ada kategori. Tambahkan lebih dulu lewat menu Kategori Artikel.' : null" />

        <x-admin.form-input name="tanggal" type="date" label="Tanggal" :value="$artikel->tanggal?->format('Y-m-d')"
            :required="true" hint="Tanggal yang tampil di artikel; artikel diurutkan dari tanggal terbaru." />

        <x-admin.form-select name="status_publikasi" label="Status" :options="StatusPublikasi::opsi()"
            :value="$artikel->status_publikasi" :required="true" :placeholder="false"
            hint="Hanya artikel berstatus Terbit yang tampil di website." />
    </x-admin.card>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.artikel.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
