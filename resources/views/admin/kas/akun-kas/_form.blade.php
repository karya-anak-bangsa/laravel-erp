@use('App\Enums\Kas\JenisAkunKas')

{{-- Dipakai create & edit; $akunKas = model baru (create) atau yang diubah (edit). --}}
<div class="form-row">
    <x-admin.form-input name="nama_akun" label="Nama Akun" :value="$akunKas->nama_akun" :required="true" maxlength="100"
        placeholder="mis. Rekening BCA Operasional" />
    <x-admin.form-select name="jenis_akun" label="Jenis Akun" :options="JenisAkunKas::opsi()" :value="$akunKas->jenis_akun" :required="true" />
</div>

<div class="form-row">
    <x-admin.form-input name="nama_bank" label="Nama Bank / Penyedia" :value="$akunKas->nama_bank" maxlength="100"
        placeholder="mis. BCA, DANA" hint="Wajib untuk akun bank. Diabaikan untuk kas tunai." />
    <x-admin.form-input name="nomor_rekening" label="Nomor Rekening / Akun" :value="$akunKas->nomor_rekening" maxlength="50"
        inputmode="numeric" hint="Wajib untuk akun bank. Untuk e-wallet boleh nomor ponsel." />
</div>

<div class="form-row">
    <x-admin.form-input name="saldo_awal" label="Saldo Awal (Rp)" :value="$akunKas->saldo_awal" :required="true"
        data-rupiah inputmode="decimal" autocomplete="off" placeholder="0"
        hint="Titik pemisah ribuan muncul otomatis; pakai koma untuk sen, mis. 1.250.000,50." />
    <x-admin.form-input name="tanggal_saldo_awal" type="date" label="Tanggal Saldo Awal" :value="$akunKas->tanggal_saldo_awal?->format('Y-m-d')"
        :required="true" :max="today()->format('Y-m-d')" hint="Tanggal saat saldo awal dihitung." />
</div>

<x-admin.form-textarea name="keterangan" label="Keterangan" :value="$akunKas->keterangan" rows="3" maxlength="1000" />

<x-admin.form-switch name="status_aktif" label="Status" text="Akun aktif" :checked="$akunKas->status_aktif"
    hint="Akun nonaktif tidak dapat dipilih saat mencatat transaksi baru." />

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.akun-kas.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
