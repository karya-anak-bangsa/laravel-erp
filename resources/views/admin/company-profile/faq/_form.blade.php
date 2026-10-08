@use('App\Models\CompanyProfile\Faq')

{{-- Dipakai create & edit; $faq = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Konten FAQ" subtitle="Tampil sebagai daftar tanya jawab di website.">
        <x-admin.form-input name="pertanyaan" label="Pertanyaan" :value="$faq->pertanyaan" :required="true" maxlength="255"
            placeholder="mis. Berapa lama proses pembuatan website?" />
        <x-admin.form-editor name="jawaban" label="Jawaban" :value="$faq->jawaban" :required="true"
            :maks="2000" hint="Jawaban yang tampil saat pertanyaan dibuka pengunjung." />
    </x-admin.card>

    <x-admin.card title="Urutan">
        <x-admin.form-input name="urutan_ke" type="number" label="Urutan ke" :value="$faq->urutan_ke" :required="true"
            min="0" :max="Faq::URUTAN_MAKS" inputmode="numeric"
            hint="Angka kecil tampil lebih dulu. Angka yang sama diurutkan berdasarkan pertanyaan." />
    </x-admin.card>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.faq.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
