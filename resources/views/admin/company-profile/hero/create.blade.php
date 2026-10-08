@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Hero' => route('admin.hero.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Tambah Hero" pretitle="Company Profile" />

    <form method="POST" action="{{ route('admin.hero.store') }}" enctype="multipart/form-data" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan hero baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.hero._form')
    </form>
@endsection
