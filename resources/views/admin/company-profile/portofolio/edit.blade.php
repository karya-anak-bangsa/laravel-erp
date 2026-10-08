@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Portofolio' => route('admin.portofolio.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.portofolio.update', $portofolio) }}" enctype="multipart/form-data" novalidate
        data-confirm="Perubahan portofolio “{{ $portofolio->judul }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.portofolio._form')
    </form>
@endsection
