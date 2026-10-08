@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Artikel' => route('admin.artikel.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.artikel.store') }}" enctype="multipart/form-data" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan artikel baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.artikel._form')
    </form>
@endsection
