@use('App\Enums\Kas\JenisTransaksi')

{{--
    Dipakai create & edit; $kategoriTransaksi = model baru (create) atau yang diubah (edit).
    $jenisTerkunci (edit) = kategori sudah dipakai transaksi, jadi jenisnya tidak bisa diganti.
--}}
@php($jenisTerkunci ??= false)

<div class="form-row">
    <x-admin.form-input name="nama_kategori" label="Nama Kategori" :value="$kategoriTransaksi->nama_kategori" :required="true" maxlength="100"
        placeholder="mis. Domain & Hosting" />
    @if ($jenisTerkunci)
        <x-admin.form-select name="jenis_transaksi" label="Jenis Transaksi" :required="true" :placeholder="false"
            :options="[$kategoriTransaksi->jenis_transaksi->value => $kategoriTransaksi->jenis_transaksi->label()]" :value="$kategoriTransaksi->jenis_transaksi"
            hint="Terkunci karena kategori sudah dipakai transaksi." />
    @else
        <x-admin.form-select name="jenis_transaksi" label="Jenis Transaksi" :options="JenisTransaksi::opsi()" :value="$kategoriTransaksi->jenis_transaksi"
            :required="true" hint="Kategori hanya dapat dipilih untuk transaksi dengan jenis yang sama." />
    @endif
</div>

<x-admin.form-textarea name="keterangan" label="Keterangan" :value="$kategoriTransaksi->keterangan" rows="3" maxlength="1000"
    placeholder="mis. Pembelian & perpanjangan domain, hosting, SSL" />

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.kategori-transaksi.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
