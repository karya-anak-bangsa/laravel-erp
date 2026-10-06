@extends('layouts.error')

@section('code', '419')
@section('title', 'Sesi telah berakhir')
@section('message', 'Halaman sudah terlalu lama dibuka. Muat ulang halaman lalu coba lagi.')

@section('actions')
    <a href="{{ url('/login') }}" class="btn btn-primary">Masuk kembali</a>
    <a href="javascript:history.back()" class="btn btn-outline">← Kembali</a>
@endsection
