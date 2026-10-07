@extends('layouts.admin', ['breadcrumb' => [
    'Kas Perusahaan' => null,
    'Transaksi Kas' => route('admin.transaksi-kas.index'),
    'Catat' => null,
]])

@section('content')
    <x-admin.page-header title="Catat Transaksi Kas" pretitle="Kas Perusahaan" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.transaksi-kas.store') }}" enctype="multipart/form-data" novalidate
            data-confirm="Pastikan tanggal, kategori, dan jumlah sudah benar. Nomor transaksi dibuat otomatis." data-confirm-title="Simpan transaksi baru?"
            data-confirm-label="Ya, simpan" data-confirm-variant="primary">
            @csrf
            @include('admin.kas.transaksi-kas._form')
        </form>
    </x-admin.card>
@endsection
