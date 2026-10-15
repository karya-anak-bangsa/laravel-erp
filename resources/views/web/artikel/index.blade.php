@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')

@php
    $deskripsiHalaman = 'Tips, panduan, dan cerita seputar teknologi, pengembangan aplikasi, serta pelatihan IT.';
    $halamanKe = $artikel->currentPage();
@endphp

@section('judul', 'Artikel'.($halamanKe > 1 ? ' (Halaman '.$halamanKe.')' : ''))
@section('deskripsi', $deskripsiHalaman)
{{-- Halaman 2 dst. kanonis ke dirinya sendiri (anjuran Google untuk paginasi); ?page=1 sama dengan /artikel. --}}
@section('kanonis', Seo::alamat(route('artikel.index', $halamanKe > 1 ? ['page' => $halamanKe] : [], false)))

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::daftarArtikel()" />
@endpush

@section('konten')
    <x-web.kepala-halaman judul="Wawasan & kabar terbaru" :jejak="['Beranda' => route('beranda'), 'Artikel' => null]">
        {{ $deskripsiHalaman }}
    </x-web.kepala-halaman>

    <div class="isi-halaman">
        <div class="wadah">
            @if ($artikel->isEmpty())
                <p class="kosong-halaman">Belum ada artikel yang diterbitkan. Silakan kunjungi lagi nanti.</p>
            @else
                {{-- Full Color: grid kartu seragam; Monochrome: daftar editorial seperti di beranda. --}}
                <div class="grid-artikel">
                    @foreach ($artikel as $item)
                        <x-web.kartu-artikel :artikel="$item" tag-judul="h2" />
                    @endforeach
                </div>

                <x-web.paginasi :paginator="$artikel" />
            @endif
        </div>
    </div>

    @include('web.beranda.ajakan')
@endsection
