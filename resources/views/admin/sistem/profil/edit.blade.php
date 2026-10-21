@extends('layouts.admin', ['breadcrumb' => ['Pengaturan Sistem' => null, 'Profil Saya' => null]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <form method="POST" action="{{ route('admin.profil.update') }}" novalidate
        data-confirm="Perubahan profil Anda akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')

        <div class="row col-8-4">
            <x-admin.card title="Data Akun" subtitle="Email dipakai untuk login ke panel admin.">
                <x-admin.form-input name="nama" label="Nama" :value="$pengguna->nama" :required="true" maxlength="100" />
                <x-admin.form-input name="email" type="email" label="Email" :value="$pengguna->email" :required="true" maxlength="150"
                    autocomplete="username" />
            </x-admin.card>

            <x-admin.card title="Kata Sandi" subtitle="Kosongkan bila tidak ingin mengganti.">
                <x-admin.form-input name="password" type="password" label="Kata Sandi Baru"
                    autocomplete="new-password" hint="Minimal 8 karakter." />
                <x-admin.form-input name="password_confirmation" type="password" label="Ulangi Kata Sandi Baru"
                    autocomplete="new-password" />
            </x-admin.card>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                <x-admin.icon name="floppy-disk" />
                Simpan
            </button>
            <a href="{{ route('admin.profil.edit') }}" class="btn btn-secondary">
                <x-admin.icon name="rotate-left" />
                Batal
            </a>
        </div>
    </form>
@endsection
