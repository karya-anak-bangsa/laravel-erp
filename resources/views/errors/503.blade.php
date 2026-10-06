@extends('layouts.error')

@section('code', '503')
@section('title', 'Sedang dalam pemeliharaan')
@section('message', 'Sistem sedang diperbarui dan akan kembali dalam beberapa menit.')

@section('actions')
    <a href="javascript:location.reload()" class="btn btn-primary">Muat ulang</a>
@endsection
