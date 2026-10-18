@use('App\Http\Requests\Admin\CompanyProfile\StorePortofolioRequest')

{{-- Dipakai create & edit; $portofolio = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Konten Portofolio" subtitle="Tampil di halaman portofolio website beserta halaman detailnya.">
        <x-admin.form-input name="judul" label="Judul" :value="$portofolio->judul" :required="true" maxlength="200"
            placeholder="mis. Website Profil Sekolah Harapan Bangsa"
            :hint="$portofolio->exists
                ? 'Alamat halaman detail: /portofolio/'.$portofolio->slug.' — tetap meskipun judul diubah, agar tautan yang sudah dibagikan tidak rusak.'
                : 'Alamat halaman detail (slug) dibuat otomatis dari judul dan tidak berubah lagi setelah disimpan.'" />
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
                <img src="{{ $portofolio->gambar_url }}" alt="Gambar utama portofolio saat ini">
            </div>
        @endif
        <x-admin.form-file name="gambar" label="Gambar Utama" accept=".jpg,.jpeg,.png,.webp" :required="! $portofolio->exists"
            :hint="($portofolio->exists ? 'Kosongkan bila tidak ingin mengganti. ' : '').'Tampil di kartu portofolio dan menjadi gambar pertama carousel. JPG, PNG, atau WEBP, maks. 2 MB. Disarankan rasio 4:3, mis. 800×600 px.'" />

        @if ($portofolio->gambarPortofolio->isNotEmpty())
            <div class="form-group">
                <div class="form-label">Gambar Tambahan Saat Ini</div>
                <ul class="galeri-admin">
                    @foreach ($portofolio->gambarPortofolio as $gambar)
                        <li>
                            <img src="{{ $gambar->gambar_url }}" alt="Gambar tambahan {{ $loop->iteration }}" loading="lazy">
                            <label class="form-check">
                                <input type="checkbox" name="hapus_galeri[]" value="{{ $gambar->id_gambar_portofolio }}"
                                    aria-label="Hapus gambar tambahan {{ $loop->iteration }}"
                                    @checked(in_array((string) $gambar->id_gambar_portofolio, (array) old('hapus_galeri', []), true))>
                                Hapus
                            </label>
                        </li>
                    @endforeach
                </ul>
                <div class="form-help">Gambar yang dicentang dihapus saat perubahan disimpan.</div>
                @error('hapus_galeri.*')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        @endif
        <x-admin.form-file name="galeri[]" label="Gambar Tambahan" accept=".jpg,.jpeg,.png,.webp" multiple
            :hint="'Tampil di carousel halaman detail setelah gambar utama, berurutan sesuai waktu unggah. Setiap portofolio paling banyak '.StorePortofolioRequest::MAKS_GAMBAR.' gambar termasuk gambar utama. JPG, PNG, atau WEBP, maks. 2 MB per gambar.'" />
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
