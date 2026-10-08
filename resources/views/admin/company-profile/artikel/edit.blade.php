@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Artikel' => route('admin.artikel.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.artikel.update', $artikel) }}" enctype="multipart/form-data" novalidate
        data-confirm="Perubahan artikel “{{ $artikel->judul }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.artikel._form')
    </form>
@endsection
