@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Layanan' => route('admin.layanan.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.layanan.store') }}" enctype="multipart/form-data" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan layanan baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.layanan._form')
    </form>
@endsection
