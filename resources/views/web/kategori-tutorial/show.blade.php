@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')

@php
    $deskripsiHalaman = 'Kumpulan tutorial dalam kategori '.$kategoriTutorial->nama_kategori.', terbaru lebih dulu.';
    $halamanKe = $tutorial->currentPage();
@endphp

@section('judul', 'Tutorial '.$kategoriTutorial->nama_kategori.($halamanKe > 1 ? ' (Halaman '.$halamanKe.')' : ''))
@section('deskripsi', $deskripsiHalaman)
{{-- Dari slug tersimpan, bukan alamat yang diketik pengunjung; halaman 2 dst. kanonis ke dirinya sendiri seperti daftar tutorial. --}}
@section('kanonis', Seo::alamat(route('kategori-tutorial.show', ['kategoriTutorial' => $kategoriTutorial] + ($halamanKe > 1 ? ['page' => $halamanKe] : []), false)))

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::kategoriTutorial($kategoriTutorial)" />
@endpush

@section('konten')
    {{-- Remah roti sampai induknya karena nama kategori tepat di bawahnya, seperti halaman kategori artikel. --}}
    <x-web.kepala-halaman :judul="$kategoriTutorial->nama_kategori" :jejak="['Beranda' => route('beranda'), 'Tutorial' => route('tutorial.index')]">
        {{ $deskripsiHalaman }}
    </x-web.kepala-halaman>

    <div class="isi-halaman">
        <div class="wadah tata-samping">
            <div>
                @include('web.tutorial.daftar')
            </div>

            @include('web.tutorial.samping', ['kategoriAktif' => $kategoriTutorial])
        </div>
    </div>

    @include('web.beranda.ajakan')
@endsection
