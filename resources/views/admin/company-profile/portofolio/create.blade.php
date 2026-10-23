@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Portofolio' => route('admin.portofolio.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.portofolio.store')" :berkas="true" title="Simpan portofolio baru?">
        @include('admin.company-profile.portofolio._form')
    </x-admin.form>
@endsection
