@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'FAQ' => route('admin.faq.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.faq.store') }}" novalidate
        data-confirm="Pastikan data sudah benar sebelum disimpan." data-confirm-title="Simpan FAQ baru?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @include('admin.company-profile.faq._form')
    </form>
@endsection
