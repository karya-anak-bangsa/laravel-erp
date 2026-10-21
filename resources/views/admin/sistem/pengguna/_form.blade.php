@use('App\Enums\Sistem\PeranPengguna')

{{-- Dipakai create & edit; $pengguna = model baru (create) atau yang diubah (edit). --}}
<div class="row col-8-4">
    <x-admin.card title="Data Pengguna" subtitle="Email dipakai untuk login ke panel admin.">
        <x-admin.form-input name="nama" label="Nama" :value="$pengguna->nama" :required="true" maxlength="100" />
        {{-- autocomplete dimatikan agar browser tidak mengisi email & kata sandi admin yang sedang login. --}}
        <x-admin.form-input name="email" type="email" label="Email" :value="$pengguna->email" :required="true" maxlength="150"
            autocomplete="off" placeholder="mis. nama@karyaanakbangsa.co.id" />
        <x-admin.form-select name="peran" label="Peran" :options="PeranPengguna::opsi()" :value="$pengguna->peran" :required="true"
            hint="Administrator mengelola Dashboard dan Company Profile; Super Administrator juga Pengaturan Sistem." />
    </x-admin.card>

    {{-- Kedua kartu selalu bersubjudul agar isian pertamanya sejajar. --}}
    <x-admin.card title="Kata Sandi" :subtitle="$pengguna->exists ? 'Kosongkan bila tidak ingin mengganti.' : 'Minimal 8 karakter.'">
        <x-admin.form-input name="password" type="password" label="Kata Sandi" :required="! $pengguna->exists"
            autocomplete="new-password" :hint="$pengguna->exists ? 'Minimal 8 karakter.' : null" />
        <x-admin.form-input name="password_confirmation" type="password" label="Ulangi Kata Sandi" :required="! $pengguna->exists"
            autocomplete="new-password" />
    </x-admin.card>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ route('admin.pengguna.index') }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
