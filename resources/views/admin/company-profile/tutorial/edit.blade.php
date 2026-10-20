@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Tutorial' => route('admin.tutorial.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.tutorial.update', $tutorial) }}" enctype="multipart/form-data" novalidate
        data-confirm="Perubahan tutorial “{{ $tutorial->judul }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.tutorial._form')
    </form>
@endsection
