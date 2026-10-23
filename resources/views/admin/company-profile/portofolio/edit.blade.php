@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Portofolio' => route('admin.portofolio.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.portofolio.update', $portofolio)" method="PUT" :berkas="true"
        :message="'Perubahan portofolio “'.$portofolio->judul.'” akan disimpan.'">
        @include('admin.company-profile.portofolio._form')
    </x-admin.form>
@endsection
