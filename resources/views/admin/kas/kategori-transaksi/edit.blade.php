@extends('layouts.admin', [
    'breadcrumb' => [
        'Kas Perusahaan' => null,
        'Kategori Transaksi' => route('admin.kategori-transaksi.index'),
        'Ubah' => null,
    ],
])

@section('content')
    <x-admin.page-header title="Ubah Kategori Transaksi" :pretitle="$kategoriTransaksi->nama_kategori" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.kategori-transaksi.update', $kategoriTransaksi) }}" novalidate
            data-confirm="Perubahan data kategori “{{ $kategoriTransaksi->nama_kategori }}” akan disimpan." data-confirm-title="Simpan perubahan?"
            data-confirm-label="Ya, simpan" data-confirm-variant="success">
            @csrf
            @method('PUT')
            @include('admin.kas.kategori-transaksi._form')
        </form>
    </x-admin.card>
@endsection
