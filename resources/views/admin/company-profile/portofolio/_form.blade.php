@use('App\Http\Requests\Admin\CompanyProfile\StorePortofolioRequest')

{{-- Dipakai create & edit; $portofolio = model baru (create) atau yang diubah (edit). --}}
@php
    // Setelah validasi gagal, baris gambar lama yang sudah dibuang tidak muncul lagi; berkas baru memang harus dipilih ulang.
    $gambarLama = session()->hasOldInput()
        ? $portofolio->gambarPortofolio->whereIn('id_gambar_portofolio', (array) old('galeri_lama', []))
        : $portofolio->gambarPortofolio;
    $galatGambarTambahan = $errors->first('galeri') ?: $errors->first('galeri.*') ?: $errors->first('galeri_lama.*');
@endphp

<div class="row col-8-4">
    <x-admin.card title="Konten Portofolio" subtitle="Tampil di halaman portofolio website beserta halaman detailnya.">
        <x-admin.form-input name="judul" label="Judul" :value="$portofolio->judul" :required="true" maxlength="200"
            placeholder="mis. Website Profil Sekolah Harapan Bangsa"
            :hint="$portofolio->exists
                ? 'Alamat halaman detail: /portofolio/'.$portofolio->slug.' — tetap meskipun judul diubah, agar tautan yang sudah dibagikan tidak rusak.'
                : 'Alamat halaman detail (slug) dibuat otomatis dari judul dan tidak berubah lagi setelah disimpan.'" />
        <x-admin.form-select name="id_kategori_portofolio" label="Kategori" :options="$opsiKategori"
            :value="$portofolio->id_kategori_portofolio" :required="true"
            :hint="$opsiKategori === [] ? 'Belum ada kategori. Tambahkan lebih dulu lewat menu Kategori Portofolio.' : null" />
        <x-admin.form-editor name="deskripsi" label="Deskripsi" :value="$portofolio->deskripsi" :required="true"
            :maks="5000" :baris="15" hint="Gambaran proyek, mis. klien, kebutuhan, fitur, dan teknologi yang dipakai." />
    </x-admin.card>

    <x-admin.card title="Gambar">
        @if ($portofolio->exists)
            <div class="pratinjau-gambar pratinjau-gambar-lebar">
                <img src="{{ $portofolio->gambar_url }}" alt="Gambar utama portofolio saat ini">
            </div>
        @endif
        <x-admin.form-file name="gambar" label="Gambar Utama" accept=".jpg,.jpeg,.png,.webp" :required="! $portofolio->exists"
            :hint="($portofolio->exists ? 'Kosongkan bila tidak ingin mengganti. ' : '').'Tampil di kartu portofolio dan menjadi gambar pertama carousel. JPG, PNG, atau WEBP, maks. 2 MB. Disarankan rasio 4:3, mis. 800×600 px.'" />

        {{-- Repeater seperti Keyword/CTA hero (pilihan pemilik 2026-10-18): gambar lama dikirim sebagai daftar yang
             dipertahankan, jadi membuang barisnya berarti menghapus gambar itu saat disimpan. --}}
        <div class="form-group" data-repeater data-repeater-maks="{{ StorePortofolioRequest::MAKS_GAMBAR - 1 }}">
            <div class="form-label">Gambar Tambahan</div>
            <div class="repeater-daftar" data-repeater-daftar>
                @foreach ($gambarLama as $gambar)
                    @include('admin.company-profile.portofolio._baris-gambar', ['i' => $loop->index, 'gambar' => $gambar])
                @endforeach
            </div>
            <div class="form-help" data-repeater-kosong @if ($gambarLama->isNotEmpty()) hidden @endif>Belum ada gambar tambahan.</div>
            <template data-repeater-templat>
                @include('admin.company-profile.portofolio._baris-gambar', ['i' => '__i__', 'gambar' => null])
            </template>
            <button type="button" class="btn btn-outline btn-sm repeater-tambah" data-repeater-tambah>
                <x-admin.icon name="plus" />
                Tambah Gambar
            </button>
            <div class="form-help">
                Tampil di carousel halaman detail setelah gambar utama, sesuai urutan daftar. Maksimal
                {{ StorePortofolioRequest::MAKS_GAMBAR - 1 }} gambar tambahan ({{ StorePortofolioRequest::MAKS_GAMBAR }} termasuk
                gambar utama). JPG, PNG, atau WEBP, maks. 2 MB per gambar.
            </div>
            @if ($galatGambarTambahan !== '')
                <div class="form-error">{{ $galatGambarTambahan }}</div>
            @endif
        </div>
    </x-admin.card>
</div>

<x-admin.form-aksi :batal="route('admin.portofolio.index')" />
