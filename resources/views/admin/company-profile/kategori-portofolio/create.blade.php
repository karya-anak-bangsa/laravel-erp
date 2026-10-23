@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Portofolio' => route('admin.kategori-portofolio.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.kategori-portofolio.store')" title="Simpan kategori portofolio baru?">
        @include('admin.company-profile.kategori-portofolio._form')
    </x-admin.form>
@endsection
