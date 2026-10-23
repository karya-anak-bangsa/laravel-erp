@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Materi Tutorial' => route('admin.materi-tutorial.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.materi-tutorial.store')" title="Simpan materi tutorial baru?">
        @include('admin.company-profile.materi-tutorial._form')
    </x-admin.form>
@endsection
