@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Manajemen Template' => route('admin.template.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <form method="POST" action="{{ route('admin.template.store') }}" novalidate data-form-template
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan template baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.template._form')
    </form>
@endsection
