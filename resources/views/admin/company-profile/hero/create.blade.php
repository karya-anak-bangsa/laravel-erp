@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Hero' => route('admin.hero.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.hero.store')" :berkas="true" title="Simpan hero baru?">
        @include('admin.company-profile.hero._form')
    </x-admin.form>
@endsection
