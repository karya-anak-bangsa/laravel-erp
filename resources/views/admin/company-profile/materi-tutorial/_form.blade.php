@use('App\Http\Requests\Admin\CompanyProfile\StoreMateriTutorialRequest')
@use('App\Models\CompanyProfile\MateriTutorial')

{{-- Dipakai create & edit; $materiTutorial = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Isi Materi" subtitle="Satu bagian dari seri tutorial, dibaca berurutan dengan materi lainnya.">
        <x-admin.form-input name="judul" label="Judul" :value="$materiTutorial->judul" :required="true" maxlength="200"
            placeholder="mis. Instalasi dan Konfigurasi Laravel 13"
            :hint="match (true) {
                ! $materiTutorial->exists => 'Alamat halaman (slug) dibuat otomatis dari judul dan tidak berubah lagi setelah tutorialnya terbit.',
                $materiTutorial->slugTerkunci() => 'Alamat halaman tetap meskipun judul diubah karena tutorialnya sudah terbit, agar tautan yang sudah dibagikan tidak rusak.',
                default => 'Alamat halaman ikut berubah bila judul diubah selama tutorialnya masih draf.',
            }" />
        <x-admin.form-editor name="deskripsi" label="Isi Materi" :value="$materiTutorial->deskripsi" :required="true"
            :maks="StoreMateriTutorialRequest::PANJANG_ISI_MAKS" :judul-bagian="true" :kode="true" :setinggi-layar="true"
            hint="Gunakan sub-judul (H2/H3) untuk membagi materi, Kode untuk perintah atau nama berkas di dalam kalimat, dan Blok kode untuk contoh kode program." />
    </x-admin.card>

    <x-admin.card title="Tutorial & Urutan">
        @if ($materiTutorial->exists)
            {{-- Nonaktif sehingga tidak terkirim; UpdateMateriTutorialRequest memang tidak menerima id_tutorial. --}}
            <x-admin.form-select name="id_tutorial" label="Tutorial" :options="$opsiTutorial"
                :value="$materiTutorial->id_tutorial" :required="true" :placeholder="false" disabled
                hint="Tutorial tidak bisa diganti setelah materi dibuat karena alamat materi memuat alamat tutorialnya." />
        @else
            <x-admin.form-select name="id_tutorial" label="Tutorial" :options="$opsiTutorial"
                :value="$materiTutorial->id_tutorial" :required="true"
                :hint="$opsiTutorial === [] ? 'Belum ada tutorial. Tambahkan lebih dulu lewat menu Daftar Tutorial.' : 'Tidak bisa diganti setelah materi disimpan.'" />
        @endif

        <x-admin.form-input name="urutan_ke" type="number" label="Urutan ke" :value="$materiTutorial->urutan_ke"
            :required="$materiTutorial->exists" min="0" :max="MateriTutorial::URUTAN_MAKS" inputmode="numeric"
            :hint="'Angka kecil tampil lebih dulu; angka yang sama diurutkan menurut waktu dibuat.'.($materiTutorial->exists ? '' : ' Kosongkan untuk menaruh materi setelah materi terakhir tutorialnya.')" />
    </x-admin.card>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.materi-tutorial.index', array_filter(['tutorial' => $materiTutorial->id_tutorial])) }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
