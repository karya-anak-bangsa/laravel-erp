@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Layanan' => route('admin.layanan.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.layanan.update', $layanan)" method="PUT" :berkas="true"
        :message="'Perubahan layanan “'.$layanan->judul.'” akan disimpan.'">
        @include('admin.company-profile.layanan._form')
    </x-admin.form>
@endsection
