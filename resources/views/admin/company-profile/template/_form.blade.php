@use('App\Enums\CompanyProfile\BayanganTemplate')
@use('App\Enums\CompanyProfile\FontTemplate')
@use('App\Enums\CompanyProfile\JenisTemplate')
@use('App\Enums\CompanyProfile\KetebalanJudul')
@use('App\Enums\CompanyProfile\LebarKonten')
@use('App\Enums\CompanyProfile\NadaDasar')
@use('App\Enums\CompanyProfile\PengaturanTampilan')
@use('App\Enums\CompanyProfile\SkalaTemplate')
@use('App\Enums\CompanyProfile\SudutTemplate')

{{--
    Dipakai create & edit; $template = model baru (create) atau yang diubah (edit), $pengaturan = pilihan awal
    Default/Custom, $bawaan = nilai bawaan kedua jenis (TemplateController::dataForm()).
--}}
@php
    // Template aktif tidak bisa dinonaktifkan langsung atau diganti jenisnya (UpdateTemplateRequest).
    $terkunci = $template->exists && $template->status_aktif;
    // Hanya isian jenis terpilih yang tampil; resources/js/admin/template.js menukarnya saat jenis diganti.
    $jenisTerpilih = old('jenis', $template->jenis?->value);
    // Default: isian warna sampai lebar konten dikunci dan diisi nilai bawaan jenis terpilih (template.js).
    $pakaiBawaan = old('pengaturan', $pengaturan->value) === PengaturanTampilan::Bawaan->value;
@endphp

<div class="row col-8-4">
    <x-admin.card title="Tampilan Template" subtitle="Warna, huruf, dan bentuk komponen di website.">
        <x-admin.form-input name="nama" label="Nama Template" :value="$template->nama" :required="true" maxlength="100"
            placeholder="mis. TKAB Full Color Biru" />

        @if ($terkunci)
            {{-- Select nonaktif tidak ikut terkirim, jadi nilainya dibawa hidden input. --}}
            <input type="hidden" name="jenis" value="{{ $template->jenis->value }}">
        @endif
        <div class="isian-berjajar">
            <x-admin.form-select name="jenis" label="Jenis" :options="JenisTemplate::opsi()" :value="$template->jenis"
                :required="true" :placeholder="false" :disabled="$terkunci" data-pilih-jenis
                :hint="$terkunci ? 'Jenis template aktif tidak bisa diubah.' : 'Full Color memakai warna merek; Monochrome memakai nada abu-abu.'" />
            <x-admin.form-select name="pengaturan" label="Pengaturan" :options="PengaturanTampilan::opsi()" :value="$pengaturan"
                :required="true" :placeholder="false" data-pilih-pengaturan
                hint="Default memakai nilai bawaan dan mengunci isian di bawah; pilih Custom untuk mengubahnya." />
        </div>

        {{-- Fieldset disabled tidak ikut terkirim; StoreTemplateRequest mengisinya lagi dari nilai bawaan. --}}
        <fieldset class="isian-tampilan" data-isian-tampilan data-bawaan="{{ json_encode($bawaan) }}" @disabled($pakaiBawaan)>
            <div data-khusus-jenis="{{ JenisTemplate::FullColor->value }}" {{ $jenisTerpilih !== JenisTemplate::FullColor->value ? 'hidden' : '' }}>
                <div class="isian-berjajar">
                    <x-admin.form-warna name="warna_utama" label="Warna Utama" :value="$template->warna_utama" :required="true"
                        hint="Judul, band ajakan, dan footer." />
                    <x-admin.form-warna name="warna_aksen" label="Warna Aksen" :value="$template->warna_aksen" :required="true"
                        hint="Tombol utama, label seksi, dan tautan." />
                </div>
                <div class="form-help catatan-warna">Warna teks otomatis digelapkan bila perlu agar tetap terbaca (kontras minimal 4,5:1).</div>
            </div>
            <div data-khusus-jenis="{{ JenisTemplate::Monochrome->value }}" {{ $jenisTerpilih !== JenisTemplate::Monochrome->value ? 'hidden' : '' }}>
                <x-admin.form-select name="nada_dasar" label="Nada Dasar" :options="NadaDasar::opsi()" :value="$template->nada_dasar"
                    :required="true" :placeholder="false" hint="Tinta abu-abu pada teks, garis, tombol, band ajakan, dan footer." />
            </div>

            {{-- Berpasangan per kelompok: huruf, bentuk, lalu ukuran. --}}
            <div class="isian-berjajar">
                <x-admin.form-select name="font" label="Font" :options="FontTemplate::opsi()" :value="$template->font"
                    :required="true" :placeholder="false" hint="Huruf seluruh teks website; label kecil tetap Geist Mono." />
                <x-admin.form-select name="ketebalan_judul" label="Ketebalan Judul" :options="KetebalanJudul::opsi()" :value="$template->ketebalan_judul"
                    :required="true" :placeholder="false" hint="Judul hero, judul seksi, dan band ajakan." />
            </div>
            <div class="isian-berjajar">
                <x-admin.form-select name="sudut" label="Sudut" :options="SudutTemplate::opsi()" :value="$template->sudut"
                    :required="true" :placeholder="false" hint="Kelengkungan kartu, tombol, dan isian." />
                <x-admin.form-select name="bayangan" label="Bayangan" :options="BayanganTemplate::opsi()" :value="$template->bayangan"
                    :required="true" :placeholder="false" hint="Tombol, kartu, isian, dan bingkai gambar hero." />
            </div>
            <div class="isian-berjajar">
                <x-admin.form-select name="skala" label="Skala" :options="SkalaTemplate::opsi()" :value="$template->skala"
                    :required="true" :placeholder="false" hint="Tinggi navbar & tombol serta ukuran judul di laptop ke atas." />
                <x-admin.form-select name="lebar_konten" label="Lebar Konten" :options="LebarKonten::opsi()" :value="$template->lebar_konten"
                    :required="true" :placeholder="false" hint="Lebar isi halaman di monitor 1920px ke atas." />
            </div>
        </fieldset>
    </x-admin.card>

    <x-admin.card title="Status">
        @if ($terkunci)
            <input type="hidden" name="status_aktif" value="1">
            <div class="form-group">
                <div class="form-label">Status</div>
                <span class="status status-green">Aktif</span>
                <div class="form-help">Template ini sedang dipakai website. Untuk menggantinya, aktifkan template {{ $template->jenis->label() }} lain.</div>
            </div>
        @else
            <x-admin.form-switch name="status_aktif" label="Status" text="Pakai di website" :checked="$template->status_aktif"
                hint="Hanya satu template aktif per jenis. Mengaktifkan template ini akan menonaktifkan template lain yang sejenis." />
        @endif
    </x-admin.card>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    {{-- Membuka beranda dengan nilai isian saat ini di tab baru, tanpa menyimpan (resources/js/admin/template.js). --}}
    <button type="button" class="btn btn-info" data-pratinjau-template="{{ route('admin.template.pratinjau') }}">
        <x-admin.icon name="eye" />
        Pratinjau
    </button>
    <a href="{{ route('admin.template.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
