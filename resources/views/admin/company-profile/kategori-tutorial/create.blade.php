@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Kategori Tutorial' => route('admin.kategori-tutorial.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.kategori-tutorial.store')" title="Simpan kategori tutorial baru?">
        @include('admin.company-profile.kategori-tutorial._form')
    </x-admin.form>
@endsection
