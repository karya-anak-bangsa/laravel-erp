@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Manajemen Pengguna' => route('admin.pengguna.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.form :action="route('admin.pengguna.update', $pengguna)" method="PUT"
        :message="'Perubahan akun “'.$pengguna->nama.'” akan disimpan.'">
        @include('admin.sistem.pengguna._form')
    </x-admin.form>
@endsection
