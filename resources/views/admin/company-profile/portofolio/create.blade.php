@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Portofolio' => route('admin.portofolio.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.portofolio.store') }}" enctype="multipart/form-data" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan portofolio baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.portofolio._form')
    </form>
@endsection
