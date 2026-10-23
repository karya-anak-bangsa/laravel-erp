@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Layanan' => route('admin.layanan.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.layanan.store')" :berkas="true" title="Simpan layanan baru?">
        @include('admin.company-profile.layanan._form')
    </x-admin.form>
@endsection
