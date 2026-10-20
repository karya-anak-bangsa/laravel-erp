@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')

@php
    $deskripsiHalaman = 'Seri materi berurutan yang mencontohkan isi program pelatihan IT, sertifikasi IT, dan bootcamp kami.';
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
                @if ($tutorial->isEmpty())
                    <p class="kosong-halaman">Belum ada tutorial. Silakan kunjungi lagi nanti.</p>
                @else
                    {{-- Grid daftar portofolio: dua kartu per baris mulai 768px, tanpa filter jenis program. --}}
                    <ul class="grid-portofolio">
                        @foreach ($tutorial as $item)
                            <li><x-web.kartu-tutorial :tutorial="$item" /></li>
                        @endforeach
                    </ul>

                    <x-web.paginasi :paginator="$tutorial" />
                @endif
            </div>

            @include('web.tutorial.samping')
        </div>
    </div>

    @include('web.beranda.ajakan')
@endsection
