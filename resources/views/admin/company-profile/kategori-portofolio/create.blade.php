@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Portofolio' => route('admin.kategori-portofolio.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.kategori-portofolio.store') }}" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan kategori portofolio baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.kategori-portofolio._form')
    </form>
@endsection
