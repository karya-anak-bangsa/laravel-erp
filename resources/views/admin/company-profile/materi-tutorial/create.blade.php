@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Materi Tutorial' => route('admin.materi-tutorial.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.materi-tutorial.store') }}" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan materi tutorial baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.materi-tutorial._form')
    </form>
@endsection
