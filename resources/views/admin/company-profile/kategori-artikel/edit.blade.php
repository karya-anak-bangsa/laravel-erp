@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Artikel' => route('admin.kategori-artikel.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.kategori-artikel.update', $kategoriArtikel) }}" novalidate
        data-confirm="Perubahan kategori “{{ $kategoriArtikel->nama_kategori }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.kategori-artikel._form')
    </form>
@endsection
