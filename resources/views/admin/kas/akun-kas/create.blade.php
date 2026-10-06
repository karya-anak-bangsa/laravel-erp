@extends('layouts.admin', ['breadcrumb' => [
    'Kas Perusahaan' => null,
    'Akun Kas' => route('admin.akun-kas.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Tambah Akun Kas" pretitle="Kas Perusahaan" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.akun-kas.store') }}" novalidate
            data-confirm="Pastikan data akun kas sudah benar sebelum disimpan." data-confirm-title="Simpan akun kas baru?"
            data-confirm-label="Ya, simpan" data-confirm-variant="primary">
            @csrf
            @include('admin.kas.akun-kas._form')
        </form>
    </x-admin.card>
@endsection
