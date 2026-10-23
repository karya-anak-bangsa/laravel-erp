@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'FAQ' => route('admin.faq.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.faq.update', $faq)" method="PUT"
        :message="'Perubahan FAQ “'.$faq->pertanyaan.'” akan disimpan.'">
        @include('admin.company-profile.faq._form')
    </x-admin.form>
@endsection
