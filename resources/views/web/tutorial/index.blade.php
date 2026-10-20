@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')

@php
    $deskripsiHalaman = 'Panduan praktis seputar pemrograman dan teknologi, disusun berurutan agar mudah diikuti dari awal.';
    $halamanKe = $tutorial->currentPage();
@endphp

@section('judul', 'Tutorial'.($halamanKe > 1 ? ' (Halaman '.$halamanKe.')' : ''))
@section('deskripsi', $deskripsiHalaman)
{{-- Halaman 2 dst. kanonis ke dirinya sendiri (anjuran Google untuk paginasi); ?page=1 sama dengan /tutorial. --}}
@section('kanonis', Seo::alamat(route('tutorial.index', $halamanKe > 1 ? ['page' => $halamanKe] : [], false)))

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::daftarTutorial()" />
@endpush

@section('konten')
    <x-web.kepala-halaman judul="Belajar langkah demi langkah" :jejak="['Beranda' => route('beranda'), 'Tutorial' => null]">
        {{ $deskripsiHalaman }}
    </x-web.kepala-halaman>

    <div class="isi-halaman">
        <div class="wadah tata-samping">
            <div>
                @include('web.tutorial.daftar')
            </div>

            @include('web.tutorial.samping')
        </div>
    </div>

    @include('web.beranda.ajakan')
@endsection
