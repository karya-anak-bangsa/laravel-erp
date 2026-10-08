@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Hero' => route('admin.hero.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.hero.update', $hero) }}" enctype="multipart/form-data" novalidate
        data-confirm="Perubahan hero “{{ $hero->judul }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.hero._form')
    </form>
@endsection
