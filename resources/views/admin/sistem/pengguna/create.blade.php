@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Manajemen Pengguna' => route('admin.pengguna.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.form :action="route('admin.pengguna.store')" title="Simpan pengguna baru?">
        @include('admin.sistem.pengguna._form')
    </x-admin.form>
@endsection
