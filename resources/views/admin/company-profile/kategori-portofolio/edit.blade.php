@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Portofolio' => route('admin.kategori-portofolio.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.kategori-portofolio.update', $kategoriPortofolio) }}" novalidate
        data-confirm="Perubahan kategori “{{ $kategoriPortofolio->nama_kategori }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.kategori-portofolio._form')
    </form>
@endsection
