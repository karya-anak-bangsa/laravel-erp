@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'FAQ' => route('admin.faq.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.form :action="route('admin.faq.store')" title="Simpan FAQ baru?">
        @include('admin.company-profile.faq._form')
    </x-admin.form>
@endsection
