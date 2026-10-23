@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Portofolio' => route('admin.kategori-portofolio.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.kategori-portofolio.update', $kategoriPortofolio)" method="PUT"
        :message="'Perubahan kategori “'.$kategoriPortofolio->nama_kategori.'” akan disimpan.'">
        @include('admin.company-profile.kategori-portofolio._form')
    </x-admin.form>
@endsection
