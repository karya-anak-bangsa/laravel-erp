@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')

@php
    $deskripsiHalaman = 'Kumpulan artikel dalam kategori '.$kategoriArtikel->nama_kategori.', terbaru lebih dulu.';
    $halamanKe = $artikel->currentPage();
@endphp

@section('judul', 'Artikel '.$kategoriArtikel->nama_kategori.($halamanKe > 1 ? ' (Halaman '.$halamanKe.')' : ''))
@section('deskripsi', $deskripsiHalaman)
{{-- Dari slug tersimpan, bukan alamat yang diketik pengunjung; halaman 2 dst. kanonis ke dirinya sendiri seperti daftar artikel. --}}
@section('kanonis', Seo::alamat(route('kategori-artikel.show', ['kategoriArtikel' => $kategoriArtikel] + ($halamanKe > 1 ? ['page' => $halamanKe] : []), false)))

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::kategoriArtikel($kategoriArtikel)" />
@endpush

@section('konten')
    {{-- Remah roti sampai induknya karena nama kategori tepat di bawahnya, seperti detail artikel. --}}
    <x-web.kepala-halaman :judul="$kategoriArtikel->nama_kategori" :jejak="['Beranda' => route('beranda'), 'Artikel' => route('artikel.index')]">
        {{ $deskripsiHalaman }}
    </x-web.kepala-halaman>

    <div class="isi-halaman">
        <div class="wadah tata-samping">
            <div>
                @include('web.artikel.daftar')
            </div>

            @include('web.artikel.samping', ['kategoriAktif' => $kategoriArtikel])
        </div>
    </div>

    @include('web.beranda.ajakan')
@endsection
