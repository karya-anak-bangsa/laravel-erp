@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Tutorial' => route('admin.kategori-tutorial.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.kategori-tutorial.update', $kategoriTutorial) }}" novalidate
        data-confirm="Perubahan kategori “{{ $kategoriTutorial->nama_kategori }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.kategori-tutorial._form')
    </form>
@endsection
