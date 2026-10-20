@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Materi Tutorial' => route('admin.materi-tutorial.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.materi-tutorial.update', $materiTutorial) }}" novalidate
        data-confirm="Perubahan materi “{{ $materiTutorial->judul }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.materi-tutorial._form')
    </form>
@endsection
