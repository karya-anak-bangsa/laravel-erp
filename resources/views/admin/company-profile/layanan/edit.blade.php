@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Layanan' => route('admin.layanan.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.layanan.update', $layanan) }}" enctype="multipart/form-data" novalidate
        data-confirm="Perubahan layanan “{{ $layanan->judul }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.layanan._form')
    </form>
@endsection
