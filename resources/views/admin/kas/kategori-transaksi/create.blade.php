@extends('layouts.admin', ['breadcrumb' => [
    'Kas Perusahaan' => null,
    'Kategori Transaksi' => route('admin.kategori-transaksi.index'),
    'Tambah' => null,
]])

@section('content')
    <x-admin.page-header title="Tambah Kategori Transaksi" pretitle="Kas Perusahaan" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.kategori-transaksi.store') }}" novalidate
            data-confirm="Pastikan data kategori transaksi sudah benar sebelum disimpan." data-confirm-title="Simpan kategori transaksi baru?"
            data-confirm-label="Ya, simpan" data-confirm-variant="primary">
            @csrf
            @include('admin.kas.kategori-transaksi._form')
        </form>
    </x-admin.card>
@endsection
