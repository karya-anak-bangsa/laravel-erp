@extends('layouts.admin')

@section('content')
    <x-admin.page-header title="Dashboard" :pretitle="'Selamat datang, '.auth()->user()->nama" />

    <x-admin.card title="Ringkasan">
        <x-admin.empty-state title="Belum ada ringkasan"
            description="Ringkasan data akan tampil di halaman ini seiring modul ERP ditambahkan." />
    </x-admin.card>
@endsection
