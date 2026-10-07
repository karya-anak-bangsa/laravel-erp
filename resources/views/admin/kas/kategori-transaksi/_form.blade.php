@use('App\Enums\Kas\JenisTransaksi')

{{-- Dipakai create & edit; $kategoriTransaksi = model baru (create) atau yang diubah (edit). --}}
<div class="form-row">
    <x-admin.form-input name="nama_kategori" label="Nama Kategori" :value="$kategoriTransaksi->nama_kategori" :required="true" maxlength="100"
        placeholder="mis. Domain & Hosting" />
    <x-admin.form-select name="jenis_transaksi" label="Jenis Transaksi" :options="JenisTransaksi::opsi()" :value="$kategoriTransaksi->jenis_transaksi"
        :required="true" hint="Kategori hanya dapat dipilih untuk transaksi dengan jenis yang sama." />
</div>

<x-admin.form-textarea name="keterangan" label="Keterangan" :value="$kategoriTransaksi->keterangan" rows="3" maxlength="1000"
    placeholder="mis. Pembelian & perpanjangan domain, hosting, SSL" />

<div class="form-actions">
    <button type="submit" class="btn btn-primary">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.kategori-transaksi.index') }}" class="btn btn-outline">
        <x-admin.icon name="xmark" />
        Batal
    </a>
</div>
