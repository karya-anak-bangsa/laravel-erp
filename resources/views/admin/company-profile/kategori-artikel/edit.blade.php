@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Artikel' => route('admin.kategori-artikel.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.kategori-artikel.update', $kategoriArtikel)" method="PUT"
        :message="'Perubahan kategori “'.$kategoriArtikel->nama_kategori.'” akan disimpan.'">
        @include('admin.company-profile.kategori-artikel._form')
    </x-admin.form>
@endsection
