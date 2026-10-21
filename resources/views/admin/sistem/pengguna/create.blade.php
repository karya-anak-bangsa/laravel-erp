@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Manajemen Pengguna' => route('admin.pengguna.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <form method="POST" action="{{ route('admin.pengguna.store') }}" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan pengguna baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.sistem.pengguna._form')
    </form>
@endsection
