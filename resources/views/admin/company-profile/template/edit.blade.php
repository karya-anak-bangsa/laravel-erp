@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Manajemen Template' => route('admin.template.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.form :action="route('admin.template.update', $template)" method="PUT" data-form-template
        :message="'Perubahan template “'.$template->nama.'” akan disimpan'.($template->status_aktif ? ' dan langsung tampil di website' : '').'.'">
        @include('admin.company-profile.template._form')
    </x-admin.form>
@endsection
