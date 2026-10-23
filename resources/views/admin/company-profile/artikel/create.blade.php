@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Artikel' => route('admin.artikel.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.artikel.store')" :berkas="true" title="Simpan artikel baru?">
        @include('admin.company-profile.artikel._form')
    </x-admin.form>
@endsection
