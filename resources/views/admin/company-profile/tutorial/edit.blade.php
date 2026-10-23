@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Tutorial' => route('admin.tutorial.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.tutorial.update', $tutorial)" method="PUT" :berkas="true"
        :message="'Perubahan tutorial “'.$tutorial->judul.'” akan disimpan.'">
        @include('admin.company-profile.tutorial._form')
    </x-admin.form>
@endsection
