@extends('layouts.admin', ['breadcrumb' => ['Pengaturan Sistem' => null, 'Profil Saya' => null]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.form :action="route('admin.profil.update')" method="PUT" message="Perubahan profil Anda akan disimpan.">

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

        <x-admin.form-aksi :batal="route('admin.dashboard')" />
    </x-admin.form>
@endsection
