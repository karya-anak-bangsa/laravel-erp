@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Manajemen Pengguna' => route('admin.pengguna.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <form method="POST" action="{{ route('admin.pengguna.update', $pengguna) }}" novalidate
        data-confirm="Perubahan akun “{{ $pengguna->nama }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.sistem.pengguna._form')
    </form>
@endsection
