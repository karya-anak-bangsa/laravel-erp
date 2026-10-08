@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'FAQ' => route('admin.faq.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.faq.update', $faq) }}" novalidate
        data-confirm="Perubahan FAQ “{{ $faq->pertanyaan }}” akan disimpan." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('admin.company-profile.faq._form')
    </form>
@endsection
