@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Tutorial' => route('admin.kategori-tutorial.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.kategori-tutorial.store') }}" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan kategori tutorial baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.kategori-tutorial._form')
    </form>
@endsection
