@use('App\Http\Requests\Admin\CompanyProfile\StoreHeroRequest')

{{-- Dipakai create & edit; $hero = model baru (create) atau yang diubah (edit). --}}
@php
    // Setelah validasi gagal, baris repeater dibangun ulang dari isian sebelumnya.
    $daftarKeyword = old('keyword', $hero->keyword ?? []);
    $daftarKeyword = is_array($daftarKeyword) && $daftarKeyword !== [] ? array_values($daftarKeyword) : [''];
    $daftarCta = old('cta', $hero->cta ?? []);
    $daftarCta = is_array($daftarCta) ? array_values($daftarCta) : [];
@endphp

<div class="row col-8-4">
    <x-admin.card title="Konten Hero" subtitle="Teks utama di bagian paling atas beranda.">
        <x-admin.form-input name="judul" label="Judul" :value="$hero->judul" :required="true" maxlength="200"
            placeholder="mis. Solusi Digital & Talenta IT untuk Indonesia" />
        <x-admin.form-editor name="deskripsi" label="Deskripsi" :value="$hero->deskripsi" :required="true"
            rows="3" :maks="1000" />

        <div class="form-group" data-repeater data-repeater-maks="{{ StoreHeroRequest::MAKS_KEYWORD }}">
            <div class="form-label"><span class="required">*</span>Keyword</div>
            <div class="repeater-daftar" data-repeater-daftar>
                @foreach ($daftarKeyword as $i => $kata)
                    @include('admin.company-profile.hero._baris-keyword', ['i' => $i, 'kata' => $kata])
                @endforeach
            </div>
            <template data-repeater-templat>
                @include('admin.company-profile.hero._baris-keyword', ['i' => '__i__', 'kata' => null])
            </template>
            <button type="button" class="btn btn-outline btn-sm repeater-tambah" data-repeater-tambah>
                <x-admin.icon name="plus" />
                Tambah Keyword
            </button>
            <div class="form-help">Kata yang bergantian tampil di judul hero, maksimal {{ StoreHeroRequest::MAKS_KEYWORD }}.</div>
            @error('keyword')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" data-repeater data-repeater-maks="{{ StoreHeroRequest::MAKS_CTA }}">
            <div class="form-label">Tombol CTA</div>
            <div class="repeater-daftar" data-repeater-daftar>
                @foreach ($daftarCta as $i => $tombol)
                    @include('admin.company-profile.hero._baris-cta', ['i' => $i, 'tombol' => is_array($tombol) ? $tombol : []])
                @endforeach
            </div>
            <div class="form-help" data-repeater-kosong @if ($daftarCta !== []) hidden @endif>Belum ada tombol.</div>
            <template data-repeater-templat>
                @include('admin.company-profile.hero._baris-cta', ['i' => '__i__', 'tombol' => []])
            </template>
            <button type="button" class="btn btn-outline btn-sm repeater-tambah" data-repeater-tambah>
                <x-admin.icon name="plus" />
                Tambah Tombol
            </button>
            <div class="form-help">
                Maksimal {{ StoreHeroRequest::MAKS_CTA }} tombol. URL boleh berupa bagian halaman (#kontak), halaman website
                (/portofolio), atau alamat lengkap (https://…). Gaya utama untuk ajakan terpenting.
            </div>
            @error('cta')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>
    </x-admin.card>

    <x-admin.card title="Gambar & Status">
        @if ($hero->exists)
            <div class="pratinjau-gambar pratinjau-gambar-lebar">
                <img src="{{ $hero->gambar_url }}" alt="Gambar hero saat ini">
            </div>
        @endif
        <x-admin.form-file name="gambar" label="Gambar" accept=".jpg,.jpeg,.png,.webp" :required="! $hero->exists"
            :hint="($hero->exists ? 'Kosongkan bila tidak ingin mengganti. ' : '').'JPG, PNG, atau WEBP, maks. 2 MB. Disarankan rasio 3:2, mis. 1536×1024 px.'" />

        <x-admin.form-switch name="status_aktif" label="Status" text="Tampilkan di beranda" :checked="$hero->status_aktif"
            hint="Hanya satu hero yang aktif. Mengaktifkan hero ini akan menonaktifkan hero lain." />
    </x-admin.card>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.hero.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
