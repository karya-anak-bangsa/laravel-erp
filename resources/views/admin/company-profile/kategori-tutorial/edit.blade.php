@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Tutorial' => route('admin.kategori-tutorial.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.kategori-tutorial.update', $kategoriTutorial)" method="PUT"
        :message="'Perubahan kategori “'.$kategoriTutorial->nama_kategori.'” akan disimpan.'">
        @include('admin.company-profile.kategori-tutorial._form')
    </x-admin.form>
@endsection
