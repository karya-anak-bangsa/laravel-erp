@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Artikel' => route('admin.kategori-artikel.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.kategori-artikel.store')" title="Simpan kategori artikel baru?">
        @include('admin.company-profile.kategori-artikel._form')
    </x-admin.form>
@endsection
