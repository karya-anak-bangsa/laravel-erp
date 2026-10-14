@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Manajemen Template' => route('admin.template.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <form method="POST" action="{{ route('admin.template.update', $template) }}" novalidate data-form-template
        data-confirm="Perubahan template “{{ $template->nama }}” akan disimpan{{ $template->status_aktif ? ' dan langsung tampil di website' : '' }}."
        data-confirm-title="Simpan perubahan?" data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.template._form')
    </form>
@endsection
