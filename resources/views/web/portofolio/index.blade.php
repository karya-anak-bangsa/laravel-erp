@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')

@php
    $deskripsiHalaman = 'Proyek dan program yang telah kami kerjakan bersama klien dan mitra.';
    $halamanKe = $portofolio->currentPage();
@endphp

@section('judul', 'Portofolio'.($halamanKe > 1 ? ' (Halaman '.$halamanKe.')' : ''))
@section('deskripsi', $deskripsiHalaman)
{{-- Halaman 2 dst. kanonis ke dirinya sendiri (anjuran Google untuk paginasi); ?page=1 sama dengan /portofolio. --}}
@section('kanonis', Seo::alamat(route('portofolio.index', $halamanKe > 1 ? ['page' => $halamanKe] : [], false)))

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::daftarPortofolio()" />
@endpush

@section('konten')
    <x-web.kepala-halaman judul="Hasil karya kami" :jejak="['Beranda' => route('beranda'), 'Portofolio' => null]">
        {{ $deskripsiHalaman }}
    </x-web.kepala-halaman>

    <div class="isi-halaman">
        <div class="wadah tata-samping">
            <div>
                @if ($portofolio->isEmpty())
                    <p class="kosong-halaman">Belum ada portofolio. Silakan kunjungi lagi nanti.</p>
                @else
                    {{-- Kartu sama dengan beranda; dua per baris mulai 768px, tanpa filter kategori (keputusan pemilik 2026-10-11). --}}
                    <ul class="grid-portofolio">
                        @foreach ($portofolio as $item)
                            <li><x-web.kartu-portofolio :portofolio="$item" tag-judul="h2" /></li>
                        @endforeach
                    </ul>

                    <x-web.paginasi :paginator="$portofolio" />
                @endif
            </div>

            @include('web.portofolio.samping')
        </div>
    </div>

    @include('web.beranda.ajakan')
@endsection
