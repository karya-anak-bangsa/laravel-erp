@extends('layouts.admin', ['breadcrumb' => [
    'Kas Perusahaan' => null,
    'Akun Kas' => route('admin.akun-kas.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Ubah Akun Kas" :pretitle="$akunKas->nama_akun" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.akun-kas.update', $akunKas) }}" novalidate
            data-confirm="Perubahan data akun “{{ $akunKas->nama_akun }}” akan disimpan." data-confirm-title="Simpan perubahan?"
            data-confirm-label="Ya, simpan" data-confirm-variant="primary">
            @csrf
            @method('PUT')
            @include('admin.kas.akun-kas._form')
        </form>
    </x-admin.card>
@endsection
