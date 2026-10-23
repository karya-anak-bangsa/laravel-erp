@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Artikel' => route('admin.artikel.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.artikel.update', $artikel)" method="PUT" :berkas="true"
        :message="'Perubahan artikel “'.$artikel->judul.'” akan disimpan.'">
        @include('admin.company-profile.artikel._form')
    </x-admin.form>
@endsection
