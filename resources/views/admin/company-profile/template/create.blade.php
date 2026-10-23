@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Manajemen Template' => route('admin.template.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.form :action="route('admin.template.store')" title="Simpan template baru?" data-form-template>
        @include('admin.company-profile.template._form')
    </x-admin.form>
@endsection
