@extends('layouts.web')

@php($deskripsiHalaman = 'Tips, panduan, dan cerita seputar teknologi, pengembangan aplikasi, serta pelatihan IT.')

@section('judul', 'Artikel'.($artikel->currentPage() > 1 ? ' (Halaman '.$artikel->currentPage().')' : '').' — '.$identitas->judul_website)
@section('deskripsi', $deskripsiHalaman)

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
