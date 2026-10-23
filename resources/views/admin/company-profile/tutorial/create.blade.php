@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Tutorial' => route('admin.tutorial.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.tutorial.store')" :berkas="true" title="Simpan tutorial baru?">
        @include('admin.company-profile.tutorial._form')
    </x-admin.form>
@endsection
