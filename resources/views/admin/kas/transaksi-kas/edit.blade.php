@extends('layouts.admin', ['breadcrumb' => [
    'Kas Perusahaan' => null,
    'Transaksi Kas' => route('admin.transaksi-kas.index'),
    $transaksiKas->nomor_transaksi => route('admin.transaksi-kas.show', $transaksiKas),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Ubah Transaksi Kas" :pretitle="$transaksiKas->nomor_transaksi" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.transaksi-kas.update', $transaksiKas) }}" enctype="multipart/form-data" novalidate
            data-confirm="Perubahan transaksi {{ $transaksiKas->nomor_transaksi }} akan disimpan." data-confirm-title="Simpan perubahan?"
            data-confirm-label="Ya, simpan" data-confirm-variant="success">
            @csrf
            @method('PUT')
            @include('admin.kas.transaksi-kas._form')
        </form>
    </x-admin.card>
@endsection
