@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Artikel' => route('admin.kategori-artikel.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.kategori-artikel.store') }}" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan kategori artikel baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.kategori-artikel._form')
    </form>
@endsection
