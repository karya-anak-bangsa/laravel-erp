@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Tutorial' => route('admin.tutorial.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.tutorial.store') }}" enctype="multipart/form-data" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan tutorial baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.tutorial._form')
    </form>
@endsection
