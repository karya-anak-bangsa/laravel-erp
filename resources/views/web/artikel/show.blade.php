@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')
@use('App\Support\TeksHtml')

@php
    $ringkasan = TeksHtml::potongDiAkhirKata(TeksHtml::ringkasan($artikel->deskripsi), 160);
    // Dari slug tersimpan, bukan alamat yang diketik pengunjung (slug berhuruf besar pun menemukan artikel ini).
    // Dipakai juga oleh tombol bagikan agar yang tersebar selalu alamat utama.
    $alamatKanonis = Seo::alamat(route('artikel.show', $artikel, false));
@endphp

@section('judul', $artikel->judul)
@section('deskripsi', $ringkasan)
@section('kanonis', $alamatKanonis)
@section('jenis-og', 'article')
@section('gambar-bagikan', $artikel->gambar_url)

@push('kepala')
    <meta property="article:published_time" content="{{ $artikel->tanggal->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $artikel->updated_at?->toIso8601String() }}">
    @if ($artikel->kategoriArtikel)
        <meta property="article:section" content="{{ $artikel->kategoriArtikel->nama_kategori }}">
    @endif
    <x-web.data-terstruktur :data="DataTerstruktur::artikel($artikel, $identitas, $ringkasan)" />
@endpush

@section('konten')
    <article aria-labelledby="judul-artikel">
        <x-web.kepala-halaman class="kepala-halaman-artikel" id-judul="judul-artikel" :judul="$artikel->judul"
            :jejak="['Beranda' => route('beranda'), 'Artikel' => route('artikel.index')]">
            <x-slot:meta>
                <div class="meta-artikel">
                    @if ($artikel->kategoriArtikel)
                        <span class="lencana-artikel">{{ $artikel->kategoriArtikel->nama_kategori }}</span>
                    @endif
                    <time class="tanggal-artikel" datetime="{{ $artikel->tanggal->toDateString() }}">Diterbitkan <span class="whitespace-nowrap">{{ $artikel->tanggal->translatedFormat('j F Y') }}</span></time>
                </div>
            </x-slot>
        </x-web.kepala-halaman>

        <div class="isi-halaman">
            <div class="wadah tata-samping">
                <div class="badan-artikel">
                    {{-- Gambar utuh (rasio asli) dalam bingkai seperti gambar hero; di atas lipatan, jadi tidak lazy. --}}
                    <div class="bingkai-artikel">
                        <img src="{{ $artikel->gambar_url }}" width="800" height="600" fetchpriority="high" decoding="async" alt="{{ $artikel->judul }}">
                    </div>
                    <div class="isi-artikel konten-html">{!! $artikel->deskripsi !!}</div>
                    <x-web.bagikan :alamat="$alamatKanonis" :judul="$artikel->judul" />
                </div>

                @include('web.artikel.samping', ['artikelTerbaru' => $artikelTerbaru])
            </div>
        </div>
    </article>

    @include('web.beranda.ajakan')
@endsection
