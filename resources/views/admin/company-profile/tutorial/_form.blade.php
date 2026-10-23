@use('App\Enums\CompanyProfile\StatusPublikasi')
@use('App\Http\Requests\Admin\CompanyProfile\StoreTutorialRequest')

{{-- Dipakai create & edit; $tutorial = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Konten Tutorial" subtitle="Judul dan pengantar seri; isi pelajarannya ditulis per materi.">
        <x-admin.form-input name="judul" label="Judul" :value="$tutorial->judul" :required="true" maxlength="200"
            placeholder="mis. Dasar-Dasar Laravel 13"
            :hint="match (true) {
                ! $tutorial->exists => 'Alamat halaman (slug) dibuat otomatis dari judul dan tidak berubah lagi setelah tutorial terbit.',
                $tutorial->status_publikasi === StatusPublikasi::Terbit => 'Alamat halaman tetap meskipun judul diubah, agar tautan yang sudah dibagikan tidak rusak.',
                default => 'Alamat halaman ikut berubah bila judul diubah selama tutorial masih draf.',
            }" />
        <x-admin.form-editor name="deskripsi" label="Deskripsi" :value="$tutorial->deskripsi" :required="true"
            :maks="StoreTutorialRequest::PANJANG_DESKRIPSI_MAKS"
            hint="Pengantar seri, mis. yang akan dipelajari dan prasyaratnya." />
    </x-admin.card>

    <x-admin.card title="Sampul & Publikasi">
        @if ($tutorial->exists)
            <div class="pratinjau-gambar pratinjau-gambar-lebar">
                <img src="{{ $tutorial->gambar_url }}" alt="Gambar sampul saat ini">
            </div>
        @endif
        <x-admin.form-file name="gambar" label="Gambar Sampul" accept=".jpg,.jpeg,.png,.webp" :required="! $tutorial->exists"
            :hint="($tutorial->exists ? 'Kosongkan bila tidak ingin mengganti. ' : '').'JPG, PNG, atau WEBP, maks. 2 MB. Disarankan rasio ±2:1, mis. 1200×630 px (ukuran pratinjau media sosial). Dipakai juga semua materinya.'" />

        <x-admin.form-select name="id_kategori_tutorial" label="Kategori" :options="$opsiKategori"
            :value="$tutorial->id_kategori_tutorial" :required="true"
            :hint="$opsiKategori === [] ? 'Belum ada kategori. Tambahkan lebih dulu lewat menu Kategori Tutorial.' : null" />

        <x-admin.form-select name="status_publikasi" label="Status" :options="StatusPublikasi::opsi()"
            :value="$tutorial->status_publikasi" :required="true" :placeholder="false"
            hint="Tutorial berstatus Terbit tampil di website setelah punya minimal satu materi." />
    </x-admin.card>
</div>

<x-admin.form-aksi :batal="route('admin.tutorial.index')" />
