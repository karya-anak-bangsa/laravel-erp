@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Hero' => route('admin.hero.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.hero.update', $hero)" method="PUT" :berkas="true"
        :message="'Perubahan hero “'.$hero->judul.'” akan disimpan.'">
        @include('admin.company-profile.hero._form')
    </x-admin.form>
@endsection
