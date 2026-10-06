@extends('layouts.admin', ['breadcrumb' => [
    'Kas Perusahaan' => null,
    'Akun Kas' => route('admin.akun-kas.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Ubah Akun Kas" :pretitle="$akunKas->nama_akun" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.akun-kas.update', $akunKas) }}" novalidate>
            @csrf
            @method('PUT')
            @include('admin.kas.akun-kas._form')
        </form>
    </x-admin.card>
@endsection
