@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Materi Tutorial' => route('admin.materi-tutorial.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.materi-tutorial.update', $materiTutorial)" method="PUT"
        :message="'Perubahan materi “'.$materiTutorial->judul.'” akan disimpan.'">
        @include('admin.company-profile.materi-tutorial._form')
    </x-admin.form>
@endsection
