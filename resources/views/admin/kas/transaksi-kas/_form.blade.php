{{--
    Dipakai create & edit; $transaksiKas = model baru (create) atau yang diubah (edit).
    $opsiAkun = [id => nama], $opsiKategori = ['Pemasukan' => [id => nama], ...].
--}}
<div class="form-row">
    <x-admin.form-input name="tanggal_transaksi" type="date" label="Tanggal Transaksi" :value="$transaksiKas->tanggal_transaksi?->format('Y-m-d')"
        :required="true" :max="today()->format('Y-m-d')" hint="Bulan pada nomor transaksi mengikuti tanggal ini." />
    <x-admin.form-select name="id_akun_kas" label="Akun Kas" :options="$opsiAkun" :value="$transaksiKas->id_akun_kas" :required="true"
        hint="Hanya akun aktif yang dapat dipilih." />
</div>

<div class="form-row">
    @if ($transaksiKas->exists)
        <x-admin.form-select name="id_kategori_transaksi" label="Kategori" :options="$opsiKategori" :value="$transaksiKas->id_kategori_transaksi"
            :required="true" :hint="'Jenis transaksi tidak dapat diubah, jadi hanya kategori '.mb_strtolower($transaksiKas->jenis_transaksi->label()).' yang ditampilkan.'" />
    @else
        <x-admin.form-select name="id_kategori_transaksi" label="Kategori" :options="$opsiKategori" :value="$transaksiKas->id_kategori_transaksi"
            :required="true" hint="Jenis transaksi (pemasukan/pengeluaran) mengikuti kategori yang dipilih." />
    @endif
    <x-admin.form-input name="jumlah" label="Jumlah (Rp)" :value="$transaksiKas->jumlah" :required="true"
        data-rupiah inputmode="decimal" autocomplete="off" placeholder="0"
        hint="Titik pemisah ribuan muncul otomatis; pakai koma untuk sen." />
</div>

<x-admin.form-input name="nama_pihak" label="Nama Pihak" :value="$transaksiKas->nama_pihak" maxlength="150"
    placeholder="mis. Hostinger, Notaris, nama klien" hint="Vendor atau klien yang terkait transaksi (opsional)." />

<x-admin.form-textarea name="keterangan" label="Keterangan" :value="$transaksiKas->keterangan" :required="true" rows="3" maxlength="1000"
    placeholder="mis. Perpanjangan domain karyaanakbangsa.co.id 1 tahun" />

<x-admin.form-file name="bukti_transaksi" label="Bukti Transaksi" accept=".jpg,.jpeg,.png,.webp,.pdf"
    :berkas-saat-ini="$transaksiKas->bukti_transaksi ? route('admin.transaksi-kas.bukti', $transaksiKas) : null"
    hint="Foto/scan nota, kuitansi, atau invoice: JPG, PNG, WEBP, atau PDF, maksimal 5 MB." />

@if ($transaksiKas->bukti_transaksi)
    <x-admin.form-switch name="hapus_bukti" text="Hapus bukti saat ini" :checked="false"
        hint="Diabaikan bila Anda mengunggah bukti baru." />
@endif

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.transaksi-kas.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
